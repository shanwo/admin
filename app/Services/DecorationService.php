<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\DecorationModel;

class DecorationService extends Service
{
    protected $decorationModel;

    public function __construct()
    {
        parent::__construct();
        $this->decorationModel = new DecorationModel();
    }

    public function lists($request)
    {
        $where[] = ['is_delete', '=', 0];
        $sort = $request->input('sort_id');
        if ($sort) $where[] = ['sort_id', '=', $sort];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->decorationModel->select('id', 'sort_id', 'name', 'image', 'order')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function add($request)
    {
        $insert = [
            'sort_id' => $request->input('sort_id'),
            'name'    => $request->input('name'),
            'image'   => $request->input('image'),
            'order'   => $request->input('order', 0),
        ];
        $id = $this->decorationModel->insertGetId($insert);
        return $this->redis::HSET('decoration:' . $insert['sort_id'], $id, json_encode([
            'id' => $id, 'name' => $insert['name'], 'image' => $insert['image']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'sort_id' => $request->input('sort_id'),
            'name'    => $request->input('name'),
            'image'   => $request->input('image'),
            'order'   => $request->input('order', 0),
        ];
        $this->decorationModel->whereKey($id)->update($update);
        return $this->redis::HSET('decoration:' . $update['sort_id'], $id, json_encode([
            'id' => $id, 'name' => $update['name'], 'image' => $update['image']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function del($request)
    {
        $id = $request->input('id');
        $this->decorationModel->whereKey($id)->update(['is_delete' => 1]);
        $info = $this->decorationModel->whereKey($id)->first();
        return $this->redis::HDEL('decoration:' . $info['sort_id'], $id);
    }
}
