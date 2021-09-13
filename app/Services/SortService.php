<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\SortModel;

class SortService extends Service
{
    private $sortModel;

    public function __construct()
    {
        parent::__construct();
        $this->sortModel = new SortModel();
    }

    public function lists($request)
    {
        $where = [];
        $type = $request->input('type');
        if($type) $where[] = ['type','=',$type];
        return $this->sortModel->select('id', 'name', 'type')->where($where)->get()->toArray();
    }

    public function add()
    {

    }

    public function del()
    {

    }
}
