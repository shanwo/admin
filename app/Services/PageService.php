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

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->linkModel = new LinkModel();
        $this->userModel = new UserModel();
        $this->templateModel = new TemplateModel();
    }

    public function lists($request)
    {
        $uid = [];
        if ($request->input('vip')) {
            $user = $this->userModel->select('id')->where('type', 2)->get()->toArray();
            $uid = array_column($user, 'id');
        }
        $nickname = $request->input('nickname');
        if ($nickname) {
            $user = $this->userModel->select('id');
            if ($uid) $user = $user->whereIn('id', $uid);
            $user = $user->where('nickname', 'like', "%$user%")->get()->toArray();
            $uid = array_column($user, 'id');
        }
        $where[] = ['is_delete', '=', 0];
        $open = $request->input('open');
        if ($open) $where[] = ['is_open', '=', $open];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['head_data', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->pageModel->select('id', 'preview', 'type', 'order')->where($where);
        if ($uid) $lists = $lists->whereIn('user_id', $uid);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        $data['lists'] = $lists;
        return $data;
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $info = $this->pageModel->select('id', 'user_id','is_open', 'browse')->whereKey($id)->first();
        $delete = $request->input('delete', 0);
        if($delete){
            $this->redis::ZREM('hot', $id);
            $this->redis::SREM('recent', $id);
            $this->redis::DEL('list_pages:' . $id);
            $this->redis::DEL('info_pages:' . $id);
            $this->redis::HDEL('user_pages:' . $info['user_id'], $id);
            $this->userModel->whereKey($info['user_id'])->decrement('pages');
            $update['is_delete'] = $delete;
        }else{
            $update['sort'] = $request->input('sort');
            $this->pageModel->whereKey($id)->update($update);
            $hot = $request->input('hot');
            $recent = $request->input('recent');
            if ($hot || $recent) {
                if (!$info['is_open']) {
                    return ['msg' => '用户闪页未开启广场开关'];
                }
                $this->redis::SADD('recent', $id);
                $this->redis::ZADD('hot', $info['browse'] + $update['sort'], $id);
            }
        }
    }
}
