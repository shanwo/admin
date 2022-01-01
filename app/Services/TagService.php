<?php

namespace App\Services;

use App\Models\TagModel;

class TagService extends Service
{
    protected $tagModel;

    public function __construct()
    {
        parent::__construct();
        $this->tagModel = new TagModel();
    }

    public function lists($request)
    {
        $where[] = ['is_delete', '=', Service::DELETE_NO];
        $type = $request->input('type');
        if ($type) $where[] = ['type', '=', $type];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->tagModel->select('id', 'name', 'mark', 'type', 'default', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        return $this->tagModel->whereKey($id)->first();
    }

    public function add($request)
    {
        $insert = [
            'name'    => $request->input('name'),
            'mark'    => $request->input('mark'),
            'type'    => $request->input('type'),
            'default' => $request->input('default', ''),
        ];
        return $this->tagModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'name'    => $request->input('name'),
            'mark'    => $request->input('mark'),
            'type'    => $request->input('type'),
            'default' => $request->input('default', ''),
            'status'  => -1

        ];
        return $this->tagModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $info = $this->tagModel->whereKey($id)->first();
        $status = $request->input('status');
        if ($status == 1) {
            $this->redis::HSET('tags', $info['mark'], $id);
        } else {
            $this->redis::HDEL('tags', $info['mark']);
        }
        return $this->tagModel->whereKey($id)->update(['status' => $status]);
    }
}