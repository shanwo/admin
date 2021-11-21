<?php
/**
 * User: Powerless
 * Date: 2021/7/18
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\TutorialsModel;

class TutorialsService extends Service
{
    protected $tutorialsModel;

    public function __construct()
    {
        parent::__construct();
        $this->tutorialsModel = new TutorialsModel();
    }

    public function lists($request)
    {
        $where[] = ['is_delete', '=', 0];
        $sort = $request->input('sort_id');
        if ($sort) $where[] = ['sort_id', '=', $sort];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->tutorialsModel->select('id', 'sort_id', 'name', 'image', 'order', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->tutorialsModel->whereKey($id)->first();
        $info['image'] = json_decode($info['image'], true) ?? [];
        return $info;
    }

    public function add($request)
    {
        $insert = [
            'sort_id' => $request->input('sort_id'),
            'name'    => $request->input('name'),
            'image'   => $request->input('image'),
            'order'   => $request->input('order', 0),
        ];
        $id = $this->tutorialsModel->insertGetId($insert);
        return $this->redis::HSET('tutorials:' . $insert['sort_id'], $id, json_encode([
            'id' => $id, 'sort_id' => $insert['sort_id'], 'name' => $insert['name'], 'image' => $insert['image']
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
        $this->tutorialsModel->whereKey($id)->update($update);
        return $this->redis::HSET('tutorials:' . $update['sort_id'], $id, json_encode([
            'id' => $id, 'sort_id' => $update['sort_id'], 'name' => $update['name'], 'image' => $update['image']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function del($request)
    {
        $id = $request->input('id');
        $this->tutorialsModel->whereKey($id)->update(['is_delete' => 1]);
        $info = $this->tutorialsModel->whereKey($id)->first();
        return $this->redis::HDEL('tutorials:' . $info['sort_id'], $id);
    }
}
