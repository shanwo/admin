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
        $id = $this->sortModel->insertGetId($insert);
        unset($insert['order']);
        return $this->redis::HSET('sort_type:' . $insert['type'], $id, json_encode([
            'id' => $id, 'name' => $insert['name'], 'type' => $insert['type']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function del($request)
    {
        $id = $request->input('id');
        $this->sortModel->whereKey($id)->delete();
        $info = $this->sortModel->whereKey($id)->first();
        return $this->redis::HDEL('sort_type:' . $info['type'], $id);
    }
}
