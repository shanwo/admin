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
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['is_delete', '=', 0];
        $sort = $request->input('sort_id');
        if ($sort) $where[] = ['sort_id', '=', $sort];
        $lists = $this->linkModel->select('id', 'name', 'image', 'url', 'number', 'order', 'type')->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
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
            'svg'         => $request->input('svg',''),
            'url'         => $request->input('url'),
            'order'       => $request->input('order'),
            'description' => $request->input('description',''),
            'type'        => $request->input('type'),
        ];
        $this->linkModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $insert = [
            'sort_id'     => $request->input('sort_id'),
            'name'        => $request->input('name'),
            'image'       => $request->input('image'),
            'svg'         => $request->input('svg'),
            'url'         => $request->input('url'),
            'order'       => $request->input('order'),
            'description' => $request->input('description'),
            'type'        => $request->input('type'),
        ];
        $this->linkModel->insertGetId($insert);
    }

    public function del($request)
    {
        $id = $request->input('id');
        return $this->linkModel->whereKey($id)->update(['is_delete' => 1]);
    }
}
