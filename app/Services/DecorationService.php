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
        $lists = $this->decorationModel->select('id', 'name', 'image', 'order')->where($where);
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
        return $this->decorationModel->insertGetId($insert);
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
        return $this->decorationModel->whereKey($id)->update($update);
    }

    public function del($request)
    {
        $id = $request->input('id');
        return $this->decorationModel->whereKey($id)->update(['is_delete' => 1]);
    }
}
