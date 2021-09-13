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
        $where[] = ['is_delete', '=', 0];
        $uid = $request->input('uid',0);
        if($uid) $where[] = ['user_id', '=', $uid];
        $lists = $this->pageModel->select('id', 'user_id','follow', 'is_open', 'head_data', 'created_at')->where($where);
        $ids = $request->input('ids');
        if($ids) {
            $lists = $lists->whereIn('id', $ids);
        }
        $lists = $lists->get()->toArray();
        return ['lists' => $lists];
    }

    public function info($id)
    {
        $info = $this->redis::GET('info_pages:' . $id);
        $info = json_decode($info, true);
        if (!$info) {
            $info = $this->pageModel->whereKey($id)->first();
            if (!$info) return false;
            $info['head_schema'] = json_decode($info['head_schema'], true);
            $info['head_data'] = json_decode($info['head_data'], true);
            $info['center_schema'] = json_decode($info['center_schema'], true);
            $info['center_data'] = json_decode($info['center_data'], true);
            $info['bottom_schema'] = json_decode($info['bottom_schema'], true);
            $info['bottom_data'] = json_decode($info['bottom_data'], true);
            $this->redis::SET('info_pages:' . $id, json_encode($info, JSON_UNESCAPED_UNICODE));
            $head = $info['head_data'];
            $head['id'] = $id;
            $this->redis::SET('list_pages:' . $id, json_encode($head, JSON_UNESCAPED_UNICODE));
        }
        return $info;
    }

    public function edit($request)
    {
        $update = $incLcIds = $incLbIds = $decLcIds = $decLbIds = $head = [];
        $id = $request->input('id');
        $info = $this->info($id);
        $headData = $request->input('head_data', []);
        if ($headData) {
            $head['avatar'] = $headData['avatar'];
            $head['nickname'] = $headData['nickname'];
            $head['signature'] = $headData['signature'] ?? '';
            $head['badge'] = $headData['badge'] ?? '';
            $head['pendant'] = $headData['pendant'] ?? '';
            $update['head_data'] = json_encode($head, JSON_UNESCAPED_UNICODE);
            $data = [
                "id"         => $id,
                "follow"     => $info['follow'],
                "is_open"    => $info['is_open'],
                "created_at" => $info['created_at'],
            ];
            $this->redis::HSET('user_pages:' . $info['user_id'], $id, json_encode(array_merge($data, $head), JSON_UNESCAPED_UNICODE));
            $head['id'] = $id;
            $this->redis::SET('list_pages:' . $id, json_encode($head, JSON_UNESCAPED_UNICODE));
        }
        $centerData = $request->input('center_data', []);
        if ($centerData) {
            $incLcIds = array_column($centerData, 'id');
            $decLcIds = array_column($info['center_data'], 'id');
            $update['center_data'] = json_encode($centerData, JSON_UNESCAPED_UNICODE);
        }
        $bottomData = $request->input('bottom_data', []);
        if ($bottomData) {
            $incLbIds = array_column($bottomData, 'id');
            $decLbIds = array_column($info['bottom_data'], 'id');
            $update['bottom_data'] = json_encode($bottomData, JSON_UNESCAPED_UNICODE);
        }
        $this->linkModel->whereIn('id', $incLcIds)->increment('number');
        $this->linkModel->whereIn('id', $incLbIds)->increment('number');
        $this->linkModel->whereIn('id', $decLcIds)->decrement('number');
        $this->linkModel->whereIn('id', $decLbIds)->decrement('number');
        $this->redis::DEL('info_pages:' . $id);
        $where[] = ['user_id', '=', $info['user_id']];
        return $this->pageModel->whereKey($id)->where($where)->update($update);
    }

    public function status($request)
    {
        $uid = $request->user()->id;
        $where[] = ['user_id', '=', $uid];
        $update = [];
        $id = $request->input('id');
        $open = $request->input('is_open');
        if (is_numeric($open)) {
            $update['is_open'] = $open;
            if ($open) {
                $info = $this->info($id);
                $this->redis::SADD('recent', $id);
                $this->redis::ZADD('hot', $info['browse'] + $info['sort'], $id);
            } else {
                $this->redis::SREM('recent', $id);
                $this->redis::ZREM('hot', $id);
            }
        }
        $delete = $request->input('is_delete');
        if ($delete) {
            $this->redis::ZREM('hot', $id);
            $this->redis::SREM('recent', $id);
            $this->redis::DEL('list_pages:' . $id);
            $this->redis::DEL('info_pages:' . $id);
            $this->redis::HDEL('user_pages:' . $uid, $id);
            $this->userModel->whereKey($uid)->decrement('pages');
            $update['is_delete'] = $delete;
        }
        return $this->pageModel->whereKey($id)->where($where)->update($update);
    }
}
