<?php

namespace App\Services;

use App\Models\UserTagModel;

class UserTagService extends Service
{
    protected $userTagModel;

    public function __construct()
    {
        parent::__construct();
        $this->userTagModel = new UserTagModel();
    }

    public function cache()
    {
        $where[] = ['status', '<>', Service::DELETE];
        $lists = $this->userTagModel->select('user_id', 'mark')->where($where)->get()->toArray();
        foreach ($lists as $k => $v) {
            $this->redis::SADD('tag_users:' . $v['mark'], $v['user_id']);
        }
    }

    public function user($id)
    {
        $where[] = ['ut.status', '<>', Service::DELETE];
        $where[] = ['ut.user_id', '=', $id];
        return $this->userTagModel->from('user_tag as ut')
            ->select('ut.id', 'ut.value', 't.name')
            ->leftJoin('tag as t', 't.id', '=', 'ut.tag_id')
            ->where($where)
            ->get()->toArray();
    }
}
