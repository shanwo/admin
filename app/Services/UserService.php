<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\BrowseModel;
use App\Models\PageModel;
use App\Models\UserModel;
use App\Models\UserThirdModel;

class UserService extends Service
{
    protected $pageModel;
    protected $userModel;
    protected $browseModel;
    protected $userThirdModel;
    protected $userTagService;
    protected $browseService;
    protected $attentionService;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new PageModel();
        $this->userModel = new UserModel();
        $this->browseModel = new BrowseModel();
        $this->userThirdModel = new UserThirdModel();
        $this->userTagService = new UserTagService();
        $this->browseService = new BrowseService();
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
        $info['third'] = $this->userThirdModel->select('nickname', 'logins_number', 'source', 'open_id', 'login_ip', 'updated_at', 'created_at')
            ->where(['user_id' => $id])->get()->toArray();
        $info['attention'] = $this->attentionService->user($id, $request);
        $info['browse'] = $this->browseService->user($id, $request);
        $info['tags'] = $this->userTagService->user($id);
        return $info;
    }

    public function status($request)
    {
        $ids = [];
        $this->size = 2;
        $id = $request->input('id');
//        $number = $this->redis::ZCARD('user_browses_click:' . $id);
//        $sql = "UPDATE sw_page SET browse = CASE id ";
//        while ($number > 0) {
//            $start = ($this->page - 1) * $this->size;
//            $stop = ($start + $this->size) - 1;
//            $browses = $this->redis::ZRANGE('user_browses_click:' . $id, $start, $stop, ['WITHSCORES' => true]);
//            foreach ($browses as $pid => $num) {
//                $sql .= sprintf("WHEN %d THEN browse + %d ", $pid, $num);
//                $this->redis::ZRANK('page_browses_click:' . $pid,$id);
//                $ids[] = $pid;
//                $number--;
//            }
//            $this->page++;
//        }
//        $ids = implode(',', $ids);
//        \Illuminate\Support\Facades\DB::update($sql . "END WHERE id IN ($ids)");
//        $this->redis::DEL('user_browses_click:' . $id);

        $pages = $this->pageModel->select('id')->where(['user_id' => $id])->get()->toArray();
        foreach ($pages as $k => $v) {
//            $this->redis::ZREM('hot', $v['id']);
//            $this->redis::SREM('recent', $v['id']);
//            $this->redis::DEL('list_pages:' . $v['id']);
//            $this->redis::DEL('info_pages:' . $v['id']);
            $browses = [];
            $number = $this->redis::ZCARD('page_browses_click:' . $v['id']);
            while ($number > count($browses)) {
                $start = ($this->page - 1) * $this->size;
                $stop = ($start + $this->size) - 1;
                $browses += $this->redis::ZRANGE('page_browses_click:' . $v['id'], $start, $stop, ['WITHSCORES' => true]);
                $this->page++;
            }
            dump($v['id']);
            dump($browses);
//            $this->pageModel->whereKey($v['id'])->increment('browse', array_sum($browses));
            $allBrowse = $this->browseModel->select('id', 'user_id')->where('page_id', '=', $v['id'])->get()->toArray();
            $original = array_column($allBrowse, 'id', 'user_id');
            dump($original);
            $add = array_diff_key($browses, $original);
            $insert = [];
            foreach ($add as $k2 => $v2) {
                $insert[] = [
                    'user_id' => $k2,
                    'page_id' => $v['id'],
                    'click'   => $v2
                ];
            }
//            $this->browseModel->insert($insert);
            $edit = array_intersect_key($browses,$original);
            $ids = implode(',', array_keys($edit));//ids 数值不对
            dump($ids);
            dump($edit);
            $sql = "UPDATE sw_browse SET click = CASE id ";
            foreach ($edit as $uid => $num) {
                $sql .= sprintf("WHEN %d THEN click + %d ", $original[$uid], $num);
            }
            echo $sql . "END WHERE id IN ($ids)";
//            \Illuminate\Support\Facades\DB::update($sql . "END WHERE id IN ($ids)");


            $this->redis::DEL('page_browses_click:' . $v['id']);
        }
        $this->redis::DEL('user_pages:' . $id);
        $this->pageModel->where(['user_id' => $id])->update(['is_open' => 0, 'status' => -1]);
        $update = [
            'type' => $request->input('type'),
        ];
        return $this->userModel->whereKey($id)->update($update);
    }

}
