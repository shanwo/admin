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

    public function lists($request, $sids = [])
    {
        $where[] = ['is_delete', '=', 0];
        $sort = $request->input('sort_id');
        if ($sort) $where[] = ['sort_id', '=', $sort];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->tutorialsModel->select('id', 'name', 'image', 'order')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        return $this->tutorialsModel->whereKey($id)->first();
    }

    public function add($request)
    {
        $insert = [
            'sort_id'     => $request->input('sort_id'),
            'name'        => $request->input('name'),
            'image'       => $request->input('image'),
            'order'       => $request->input('order',0),
        ];
        return $this->tutorialsModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'sort_id'     => $request->input('sort_id'),
            'name'        => $request->input('name'),
            'image'       => $request->input('image'),
            'order'       => $request->input('order',0),
        ];
        return $this->tutorialsModel->whereKey($id)->update($update);
    }

    public function del($request)
    {
        $id = $request->input('id');
        return $this->tutorialsModel->whereKey($id)->update(['is_delete' => 1]);
    }
}
