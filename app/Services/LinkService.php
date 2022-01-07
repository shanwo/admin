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
        $where[] = ['is_delete', '=', Service::DELETE_NO];
        $sort = $request->input('sort_id');
        if ($sort) $where[] = ['sort_id', '=', $sort];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->linkModel->select('id', 'name', 'image', 'url', 'number', 'order', 'type', 'status', 'updated_at')->where($where);
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
        return $this->linkModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'sort_id' => $request->input('sort_id'),
            'name'    => $request->input('name'),
            'image'   => $request->input('image'),
            'url'     => $request->input('url'),
            'order'   => $request->input('order', 0),
            'type'    => $request->input('type'),
            'status'  => self::DISABLE
        ];
        $svg = $request->input('svg', '');
        if ($svg) $update['svg'] = $svg;
        $description = $request->input('description', '');
        if ($description) $update['description'] = $description;
        return $this->linkModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $info = $this->linkModel->whereKey($id)->first();
        $status = $request->input('status');
        if ($status == 1) {
            $this->redis::HSET('link:' . $info['sort_id'], $id, json_encode([
                'id' => $id, 'sort_id' => $info['sort_id'], 'name' => $info['name'], 'image' => $info['image'], 'svg' => $info['svg'] ?? '', 'url' => $info['url']
            ], JSON_UNESCAPED_UNICODE));
        } else {
            $this->redis::HDEL('link:' . $info['sort_id'], $id);
        }
        return $this->linkModel->whereKey($id)->update(['status' => $status]);
    }
}
