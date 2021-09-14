<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\LinkModel;

class LinkService extends Service
{
    protected $linkModel;

    public function __construct()
    {
        parent::__construct();
        $this->linkModel = new LinkModel();
    }

    public function lists($request)
    {
        $where[] = ['is_delete', '=', 0];
        $sort = $request->input('sort_id');
        if ($sort) $where[] = ['sort_id', '=', $sort];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->linkModel->select('id', 'name', 'image', 'url', 'number', 'order', 'type')->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        $data['lists'] = $lists;
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        return $this->linkModel->whereKey($id)->first();
    }

    public function add($request)
    {
        $insert = [
            'sort_id'     => $request->input('sort_id'),
            'name'        => $request->input('name'),
            'image'       => $request->input('image'),
            'url'         => $request->input('url'),
            'svg'         => $request->input('svg',''),
            'order'       => $request->input('order',0),
            'description' => $request->input('description',''),
            'type'        => $request->input('type'),
        ];
        return $this->linkModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'sort_id'     => $request->input('sort_id'),
            'name'        => $request->input('name'),
            'image'       => $request->input('image'),
            'url'         => $request->input('url'),
            'svg'         => $request->input('svg',''),
            'order'       => $request->input('order',0),
            'description' => $request->input('description',''),
            'type'        => $request->input('type'),
        ];
        return $this->linkModel->whereKey($id)->update($update);
    }

    public function del($request)
    {
        $id = $request->input('id');
        return $this->linkModel->whereKey($id)->update(['is_delete' => 1]);
    }
}
