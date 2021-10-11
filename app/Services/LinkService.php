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
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
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
            'svg'         => $request->input('svg', ''),
            'order'       => $request->input('order', 0),
            'description' => $request->input('description', ''),
            'type'        => $request->input('type'),
        ];
        $id = $this->linkModel->insertGetId($insert);
        return $this->redis::HSET('link:' . $insert['sort_id'], $id, json_encode([
            'id' => $id, 'sort_id' => $insert['sort_id'], 'name' => $insert['name'], 'image' => $insert['image'], 'svg' => $insert['svg'], 'url' => $insert['url']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'sort_id'     => $request->input('sort_id'),
            'name'        => $request->input('name'),
            'image'       => $request->input('image'),
            'url'         => $request->input('url'),
            'svg'         => $request->input('svg', ''),
            'order'       => $request->input('order', 0),
            'description' => $request->input('description', ''),
            'type'        => $request->input('type'),
        ];
        $this->linkModel->whereKey($id)->update($update);
        return $this->redis::HSET('link:' . $update['sort_id'], $id, json_encode([
            'id' => $id, 'sort_id' => $update['sort_id'], 'name' => $update['name'], 'image' => $update['image'], 'svg' => $update['svg'], 'url' => $update['url']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function del($request)
    {
        $id = $request->input('id');
        $this->linkModel->whereKey($id)->update(['is_delete' => 1]);
        $info = $this->linkModel->whereKey($id)->first();
        return $this->redis::HDEL('link:' . $info['sort_id'], $id);
    }
}
