<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Sw;

use App\Models\Sw\AttentionModel;
use App\Models\Sw\BrowseModel;
use App\Models\Sw\PageModel;
use App\Services\Service;

class PageService extends Service
{
    protected $pageModel;
    protected $browseModel;
    protected $attentionModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->browseModel = new BrowseModel();
        $this->attentionModel = new AttentionModel();
    }

    public function lists($request)
    {
        $where = [];
        $open = $request->input('open');
        if (is_numeric($open)) $where[] = ['is_open', '=', $open];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['user_id', '=', $uid];
        $keyword = $request->input('keyword');
        if ($keyword) {
            $where[] = ['head_data', 'like', "%$keyword%"];
            $where[] = ['code', 'like', "%$keyword%", 'or'];
        }
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->pageModel->from('sw_page as p')
            ->select('id', 'code', 'user_id', 'follow', 'browse', 'sort', 'is_open', 'head_data', 'status', 'updated_at')
            ->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $order = $request->input('order', 'id');
        $lists = $lists->orderBy($order, 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user,true);
            $v['nickname'] = $info['nickname'] ?? '';
            $v['type'] = $info['type'] ?? 0;
            $tmp = json_decode($v['head_data'], true);
            $v['name'] = $tmp['nickname'];
            unset($v['head_data']);
        }
        $data['lists'] = $lists;
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        return $this->pageModel->select('sort')->whereKey($id)->first();
    }

    public function sort($request)
    {
        $id = $request->input('id');
        $info = $this->pageModel->select('id', 'user_id', 'is_open', 'browse')->whereKey($id)->first();
        if ($info['is_open']) {
            $update['sort'] = $request->input('sort');
            $update['is_open'] = $request->input('cache', 0);
            $this->recommend($update['is_open'], $info, $update['sort']);
            return $this->pageModel->whereKey($id)->update($update);
        } else {
            return ['msg' => '用户闪页未开启广场开关'];
        }
    }

    private function recommend($cache, $info, $sort = 0)
    {
        switch ($cache) {
            case 0:
                $this->redis::ZREM('hot', $info['id']);
                $this->redis::SREM('recent', $info['id']);
                break;
            case 1:
                $this->redis::SADD('recent', $info['id']);
                $this->redis::ZADD('hot', $info['browse'] + $sort, $info['id']);
                break;
            case 2:
                $this->redis::ZADD('hot', $info['browse'] + $sort, $info['id']);
                break;
            case 3:
                $this->redis::SADD('recent', $info['id']);
                break;
        }
    }

    public function status($request)
    {
        $info = $this->info($request);
        $id = $request->input('id');
        $status = $request->input('status');
        if ($status == 1) {
            $this->recommend($info['is_open'], $info, $info['sort']);
            $this->redis::SETEX('info_pages:' . $id, rand(self::ONEDAY * 3, self::ONEDAY * 7), json_encode($info, JSON_UNESCAPED_UNICODE));
            $this->redis::HSET('pages_code', $info['code'], $id);
            $head = $info['head_data'];
            $head['code'] = $info['code'];
            $this->redis::SET('list_pages:' . $id, json_encode($head, JSON_UNESCAPED_UNICODE));
            $head['id'] = $id;
            $head['user_id'] = $info['user_id'];
            $head['follow'] = $info['follow'];
            $head['browse'] = $info['browse'];
            $head['is_open'] = $info['is_open'];
            $this->redis::HSET('user_pages:' . $info['user_id'], $id, json_encode($head, JSON_UNESCAPED_UNICODE));
        } else {
            $this->redis::ZREM('hot', $id);
            $this->redis::SREM('recent', $id);
            $this->redis::DEL('info_pages:' . $id);
            $this->redis::DEL('list_pages:' . $id);
            $this->redis::HDEL('pages_code', $info['code']);
            $this->redis::HDEL('user_pages:' . $info['user_id'], $id);
            $this->updateBrowse($id);
            $this->updateAttention($id);
        }
        return $this->pageModel->whereKey($id)->update(['status' => $status]);
    }

    /**
     * 更新缓存的浏览数入库
     * @param $id
     * @return void
     */
    public function updateBrowse($id)
    {
        $this->size = 200;
        $ids = $insert = $browses = [];
        $number = $this->redis::ZCARD('page_browses_click:' . $id);
        while ($number > count($browses)) {
            $start = ($this->page - 1) * $this->size;
            $stop = ($start + $this->size) - 1;
            $browses += $this->redis::ZRANGE('page_browses_click:' . $id, $start, $stop, true);
            $this->page++;
        }
        $this->pageModel->whereKey($id)->increment('browse', array_sum($browses));
        $allBrowse = $this->browseModel->select('id', 'user_id')->where('page_id', '=', $id)->get()->toArray();
        $original = array_column($allBrowse, 'id', 'user_id');
        $add = array_diff_key($browses, $original);
        foreach ($add as $uid => $num) {
            $insert[] = ['user_id' => $uid, 'page_id' => $id, 'click' => $num];
        }
        $this->browseModel->insert($insert);
        $edit = array_intersect_key($browses, $original);
        $sql = "UPDATE sw_browse SET click = CASE id ";
        foreach ($edit as $uid => $num) {
            $ids[] = $original[$uid];
            $sql .= sprintf("WHEN %d THEN click + %d ", $original[$uid], $num);
        }
        $ids = implode(',', $ids);
        if ($ids) {
            \Illuminate\Support\Facades\DB::update($sql . "END WHERE id IN ($ids)");
        }
        foreach ($browses as $uid => $num) {
            $this->redis::ZREM('user_browses_click:' . $uid, $id);
        }
        $this->redis::DEL('page_browses_click:' . $id);
    }

    /**
     * 更新缓存的关注数入库
     * @param $id
     * @return void
     */
    public function updateAttention($id)
    {
        $this->size = 200;
        $insert = $attentions = [];
        $number = $this->redis::ZCARD('page_attentions:' . $id);
        while ($number > count($attentions)) {
            $start = ($this->page - 1) * $this->size;
            $stop = ($start + $this->size) - 1;
            $attentions += $this->redis::ZREVRANGE('page_attentions:' . $id, $start, $stop, true);
            $this->page++;
        }
        $allAttention = $this->attentionModel->select('id', 'user_id')->where('page_id', '=', $id)->get()->toArray();
        $original = array_column($allAttention, 'id', 'user_id');
        $add = array_diff_key($attentions, $original);
        if ($add) {
            foreach ($add as $uid => $num) {
                $date = date('Y-m-d H:i:s', intval($num));
                $insert[] = ['user_id' => $uid, 'page_id' => $id, 'status' => self::ENABLE, 'updated_at' => $date, 'created_at' => $date];
            }
            $this->attentionModel->insert($insert);
        }
        $edit = array_diff_key($original, $attentions);
        if ($edit) {
            $this->attentionModel->whereIn('id', array_values($edit))->update(['status' => self::DISABLE]);
        }
        $this->pageModel->whereKey($id)->update(['follow' => count($attentions)]);
        $enable = array_intersect_key($original, $attentions);
        $this->attentionModel->whereIn('id', array_values($enable))->update(['status' => self::ENABLE]);
        foreach ($attentions as $uid => $num) {
            $this->redis::ZREM('user_attentions:' . $uid, $id);
        }
        $this->redis::DEL('page_attentions:' . $id);
    }
}
