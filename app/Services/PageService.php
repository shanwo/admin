<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\LinkModel;
use App\Models\PageModel;
use App\Models\TemplateModel;
use App\Models\UserModel;

class PageService extends Service
{
    protected $pageModel;
    protected $linkModel;
    protected $userModel;
    protected $templateModel;
    protected $browseService;
    protected $attentionService;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->linkModel = new LinkModel();
        $this->userModel = new UserModel();
        $this->templateModel = new TemplateModel();
        $this->browseService = new BrowseService();
        $this->attentionService = new AttentionService();
    }

    public function lists($request)
    {
        $where = [];
        $type = $request->input('type');
        if ($type) $where[] = ['u.type', '=', $type];
        $nickname = $request->input('nickname');
        if ($nickname) $where[] = ['u.nickname', 'like', "%$nickname%"];
        $open = $request->input('open');
        if (is_numeric($open)) $where[] = ['p.is_open', '=', $open];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['p.head_data', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->pageModel->from('sw_page as p')
            ->select('p.id', 'u.type', 'u.nickname', 'p.code', 'p.follow', 'p.browse', 'p.sort', 'p.is_open', 'p.head_data', 'p.status', 'p.updated_at')
            ->leftJoin('sw_user as u', 'p.user_id', '=', 'u.id')
            ->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $order = 'p.' . $request->input('order', 'id');
        $lists = $lists->orderBy($order, 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
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
        $info = $this->pageModel->whereKey($id)->first();
        $info['head_schema'] = json_decode($info['head_schema'], true);
        $info['head_data'] = json_decode($info['head_data'], true);
        $info['center_schema'] = json_decode($info['center_schema'], true);
        $info['center_data'] = json_decode($info['center_data'], true);
        $info['bottom_schema'] = json_decode($info['bottom_schema'], true);
        $info['bottom_data'] = json_decode($info['bottom_data'], true);
        $info['attention'] = $this->attentionService->page($id, $request);
        $info['browse'] = $this->browseService->page($id, $request);
        return $info;
    }

    public function sort($request)
    {
        $id = $request->input('id');
        $info = $this->pageModel->select('id', 'user_id', 'is_open', 'browse')->whereKey($id)->first();
        if ($info['is_open']) {
            $update['sort'] = $request->input('sort');
            $cache = $request->input('cache', 0);
            if (!$cache) {
                $update['sort'] = 0;
                $this->redis::ZREM('hot', $id);
                $this->redis::SREM('recent', $id);
            }
            if ($cache == 1 || $cache == 2) {
                $this->redis::ZADD('hot', $info['browse'] + $update['sort'], $id);
            }
            if ($cache == 1 || $cache == 3) {
                $this->redis::SADD('recent', $id);
            }
            $update['is_open'] = $request->input('cache');
            return $this->pageModel->whereKey($id)->update($update);
        } else {
            return ['msg' => '用户闪页未开启广场开关'];
        }
    }

    public function status($request)
    {
        $id = $request->input('id');
        $info = $this->pageModel->whereKey($id)->first();
        $status = $request->input('status');
        if ($status == 1) {
            if ($info['is_open'] == 1 || $info['is_open'] == 2) {
                $this->redis::ZADD('hot', $info['browse'] + $info['sort'], $id);
            }
            if ($info['is_open'] == 1 || $info['is_open'] == 3) {
                $this->redis::SADD('recent', $id);
            }
            $info['head_schema'] = json_decode($info['head_schema'], true);
            $info['head_data'] = json_decode($info['head_data'], true);
            $info['center_schema'] = json_decode($info['center_schema'], true);
            $info['center_data'] = json_decode($info['center_data'], true);
            $info['bottom_schema'] = json_decode($info['bottom_schema'], true);
            $info['bottom_data'] = json_decode($info['bottom_data'], true);
            $this->redis::SETEX('info_pages:' . $id, rand($this->oneDay * 3, $this->oneDay * 7), json_encode($info, JSON_UNESCAPED_UNICODE));
            $this->redis::HSET('pages_code', $info['code'], $id);
            $data = $info['head_data'];
            $data['code'] = $info['code'];
            $this->redis::SET('list_pages:' . $id, json_encode($data, JSON_UNESCAPED_UNICODE));
            $data['id'] = $id;
            $data['user_id'] = $info['user_id'];
            $data['follow'] = $info['follow'];
            $data['browse'] = $info['browse'];
            $data['is_open'] = $info['is_open'];
            $this->redis::HSET('user_pages:' . $info['user_id'], $id, json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            $this->redis::ZREM('hot', $id);
            $this->redis::SREM('recent', $id);
            $this->redis::DEL('info_pages:' . $id);
            $this->redis::DEL('list_pages:' . $id);
            $this->redis::HDET('pages_code', $info['code']);
            $this->redis::HDEL('user_pages:' . $info['user_id'], $id);
        }
        return $this->pageModel->whereKey($id)->update(['status' => $status]);
    }
}
