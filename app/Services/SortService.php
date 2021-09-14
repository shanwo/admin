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
        if ($type) $where[] = ['type', '=', $type];
        $data['lists'] = $this->sortModel->select('id', 'name', 'order', 'type')
            ->where($where)
            ->orderBy('order', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->toArray();
        return $data;
    }

    public function add($request)
    {
        $insert = [
            'name'  => $request->input('name'),
            'type'  => $request->input('type'),
            'order' => $request->input('order', 0),
        ];
        return $this->sortModel->insertGetId($insert);
    }

    public function del($request)
    {
        $id = $request->input('id');
        return $this->sortModel->whereKey($id)->delete();
    }
}
