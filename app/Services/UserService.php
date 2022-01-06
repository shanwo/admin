<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\PageModel;
use App\Models\UserModel;
use App\Models\UserThirdModel;

class UserService extends Service
{
    protected $pageModel;
    protected $userModel;
    protected $userThirdModel;
    protected $userTagService;
    protected $browseService;
    protected $pageService;
    protected $attentionService;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->userModel = new UserModel();
        $this->userThirdModel = new UserThirdModel();
        $this->userTagService = new UserTagService();
        $this->browseService = new BrowseService();
        $this->pageService = new PageService();
        $this->attentionService = new AttentionService();
    }

    public function lists($request)
    {
        $where = [];
        $type = $request->input('type');
        if ($type) $where[] = ['type', '=', $type];
        $area = $request->input('area');
        if ($area) $where[] = ['area_id', '=', $area];
        $keyword = $request->input('keyword');
        if ($keyword) {
            $where[] = ['contact', 'like', "%$keyword%"];
            $where[] = ['nickname', 'like', "%$keyword%", 'or'];
        }
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->userModel->select('id', 'type', 'nickname', 'sex', 'avatar', 'merger_name', 'pages', 'contact', 'expired_at', 'updated_at', 'created_at')->where($where);
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
        $info['attention'] = $this->attentionService->user($id, $request);
        $info['browse'] = $this->browseService->user($id, $request);
        $info['tags'] = $this->userTagService->user($id);
        return $info;
    }

    public function status($request)
    {
        $ids = [];
        $this->size = 200;
        $id = $request->input('id');
        $info = $this->userModel->select('token')->whereKey($id)->first();
        $this->redis::DEL('user_tags:' . $id);
        $this->redis::DEL('user_infos:' . $id);
        $this->redis::DEL('user_pages:' . $id);
        $this->redis::DEL('user_tokens:' . $info['token']);
        $number = $this->redis::ZCARD('user_browses_click:' . $id);
        $sql = "UPDATE sw_page SET browse = CASE id ";
        while ($number > 0) {
            $start = ($this->page - 1) * $this->size;
            $stop = ($start + $this->size) - 1;
            $browses = $this->redis::ZRANGE('user_browses_click:' . $id, $start, $stop, ['WITHSCORES' => true]);
            foreach ($browses as $pid => $num) {
                $sql .= sprintf("WHEN %d THEN browse + %d ", $pid, $num);
                $this->redis::ZRANK('page_browses_click:' . $pid, $id);
                $ids[] = $pid;
                $number--;
            }
            $this->page++;
        }
        $ids = implode(',', $ids);
        \Illuminate\Support\Facades\DB::update($sql . "END WHERE id IN ($ids)");
        $this->redis::DEL('user_browses:' . $id);
        $this->redis::DEL('user_browses_click:' . $id);
        $pages = $this->pageModel->select('id', 'code')->where(['user_id' => $id])->get()->toArray();
        foreach ($pages as $k => $v) {
            $this->redis::ZREM('hot', $v['id']);
            $this->redis::SREM('recent', $v['id']);
            $this->redis::DEL('list_pages:' . $v['id']);
            $this->redis::DEL('info_pages:' . $v['id']);
            $this->redis::HDET('pages_code', $v['code']);
            $this->pageService->updateNumber($v['id']);
        }
        $this->pageModel->where(['user_id' => $id])->update(['is_open' => 0, 'status' => -1]);
        $update['type'] = $request->input('type');
        return $this->userModel->whereKey($id)->update($update);
    }

}
