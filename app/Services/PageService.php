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
        $where[] = ['p.is_delete', '=', 0];
        $type = $request->input('type');
        if ($type) $where[] = ['u.type', '=', $type];
        $nickname = $request->input('nickname');
        if ($nickname) $where[] = ['u.nickname', 'like', "%$nickname%"];
        $open = $request->input('open');
        if ($open) $where[] = ['p.is_open', '=', $open];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['p.head_data', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->pageModel->from('sw_page as p')
            ->select('p.id', 'u.type', 'u.nickname', 'p.follow', 'p.browse', 'p.sort', 'p.is_open', 'p.head_data', 'p.updated_at')
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
                $this->redis::ZREM('hot', $id);
                $this->redis::SREM('recent', $id);
            }
            if ($cache == 1 || $cache == 2) {
                $this->redis::ZADD('hot', $info['browse'] + $update['sort'], $id);
            }
            if ($cache == 1 || $cache == 3) {
                $this->redis::SADD('recent', $id);
            }
            return $this->pageModel->whereKey($id)->update($update);
        } else {
            return ['msg' => '用户闪页未开启广场开关'];
        }
    }

    public function del($request)
    {
        $id = $request->input('id');
        $info = $this->pageModel->select('id', 'user_id')->whereKey($id)->first();
        $this->redis::ZREM('hot', $id);
        $this->redis::SREM('recent', $id);
        $this->redis::DEL('list_pages:' . $id);
        $this->redis::DEL('info_pages:' . $id);
        $this->redis::HDEL('user_pages:' . $info['user_id'], $id);
        $this->userModel->whereKey($info['user_id'])->decrement('pages');
        return $this->pageModel->whereKey($id)->update(['is_delete' => 1]);
    }
}
