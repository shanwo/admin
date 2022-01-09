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

    public function user($id)
    {
        $where[] = ['ut.user_id', '=', $id];
        return $this->userTagModel->from('sw_user_tag as ut')
            ->select('ut.id', 'ut.value', 't.name')
            ->leftJoin('sw_tag as t', 't.id', '=', 'ut.tag_id')
            ->where($where)
            ->get()->toArray();
    }
}