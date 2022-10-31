<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\Sw\AttentionModel;
use App\Models\Sw\PageModel;
use App\Models\UserModel;
use App\Models\UserThirdModel;
use App\Services\Sw\PageService;

class UserService extends Service
{
    protected $userModel;
    protected $pageModel;
    protected $pageService;
    protected $attentionModel;
    protected $userThirdModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->pageModel = new PageModel();
        $this->pageService = new PageService();
        $this->attentionModel = new AttentionModel();
        $this->userThirdModel = new UserThirdModel();
    }

    public function lists($request)
    {
        $where = [];
        $area = $request->input('area');
        if ($area) $where[] = ['area_id', '=', $area];
        $keyword = $request->input('keyword');
        if ($keyword) {
            $where[] = ['contact', 'like', "%$keyword%"];
            $where[] = ['nickname', 'like', "%$keyword%", 'or'];
        }
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->userModel->select('id', 'nickname', 'sex', 'avatar', 'merger_name', 'contact')->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $v['contact'] = json_decode($v['contact'], true) ?? [];
        }
        $data['lists'] = $lists;
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->userModel->whereKey($id)->first();
        $info['contact'] = json_decode($info['contact'], true) ?? [];
        $info['third'] = $this->userThirdModel->select('nickname', 'logins_number', 'source', 'open_id', 'login_ip', 'updated_at', 'created_at')->where(['user_id' => $id])->get()->toArray();
        return $info;
    }

    public function status($request)
    {
        $id = $request->input('id');
        $this->redis::DEL('user_tags:' . $id);
        $this->redis::DEL('user_infos:' . $id);
        $this->redis::DEL('user_pages:' . $id);
        $user = $this->redis::GET('user_infos:' . $id);
        $info = json_decode($user,true);
        $this->redis::DEL('user_tokens:' . $info['api_token']);
        $this->browses($id);
        $this->attention($id);
        $pages = $this->pageModel->select('id', 'code')->where(['user_id' => $id])->get()->toArray();
        foreach ($pages as $k => $v) {
            $this->redis::ZREM('hot', $v['id']);
            $this->redis::SREM('recent', $v['id']);
            $this->redis::DEL('list_pages:' . $v['id']);
            $this->redis::DEL('info_pages:' . $v['id']);
            $this->redis::HDET('pages_code', $v['code']);
            $this->pageService->updateBrowse($v['id']);
            $this->pageService->updateAttention($v['id']);
        }
        $this->pageModel->where(['user_id' => $id])->update(['is_open' => 0, 'sort' => 0, 'status' => self::DISABLE]);
        $update['type'] = $request->input('type');
        return $this->attentionModel->whereKey($id)->update($update);
    }

    /**
     * 更新缓存的用户浏览数入库
     * @param $id
     * @return void
     */
    public function browses($id)
    {
        $ids = [];
        $this->size = 200;
        $number = $this->redis::ZCARD('user_browses_click:' . $id);
        $sql = "UPDATE sw_page SET browse = CASE id ";
        while ($number > 0) {
            $start = ($this->page - 1) * $this->size;
            $stop = ($start + $this->size) - 1;
            $browses = $this->redis::ZRANGE('user_browses_click:' . $id, $start, $stop, true);
            foreach ($browses as $pid => $num) {
                $sql .= sprintf("WHEN %d THEN browse + %d ", $pid, $num);
                $this->redis::ZREM('page_browses_click:' . $pid, $id);
                $ids[] = $pid;
                $number--;
            }
            $this->page++;
        }
        $ids = implode(',', $ids);
        if ($ids) {
            \Illuminate\Support\Facades\DB::update($sql . "END WHERE id IN ($ids)");
        }
        $this->redis::DEL('user_browses:' . $id);
        $this->redis::DEL('user_browses_click:' . $id);
    }

    /**
     * 更新缓存的用户关注数入库
     * @param $id
     * @return void
     */
    public function attention($id)
    {
        $this->size = 200;
        $insert = $attentions = [];
        $number = $this->redis::ZCARD('user_attentions:' . $id);
        while ($number > count($attentions)) {
            $start = ($this->page - 1) * $this->size;
            $stop = ($start + $this->size) - 1;
            $attentions += $this->redis::ZREVRANGE('user_attentions:' . $id, $start, $stop, true);
            $this->page++;
        }
        $allAttention = $this->attentionModel->select('id', 'page_id')->where(['user_id' => $id])->get()->toArray();
        $original = array_column($allAttention, 'id', 'page_id');
        $add = array_diff_key($attentions, $original);
        if ($add) {
            foreach ($add as $pid => $num) {
                $date = date('Y-m-d H:i:s', intval($num));
                $insert[] = ['user_id' => $id, 'page_id' => $pid, 'status' => self::ENABLE, 'updated_at' => $date, 'created_at' => $date];
            }
            $this->attentionModel->insert($insert);
        }
        $edit = array_diff_key($original, $attentions);
        if ($edit) {
            $this->attentionModel->whereIn('id', array_values($edit))->update(['status' => self::DISABLE]);
        }
        $enable = array_intersect_key($original, $attentions);
        $this->pageModel->whereKey($id)->update(['follow' => count($attentions)]);
        $this->attentionModel->whereIn('id', array_values($enable))->update(['status' => self::ENABLE]);
        foreach ($attentions as $pid => $num) {
            $this->redis::ZREM('page_attentions:' . $pid, $id);
        }
    }
}
