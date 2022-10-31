<?php
/**
 * User: Powerless
 * Date: 2021/7/18
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Sw;

use App\Models\Sw\TutorialsModel;
use App\Services\Service;

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
        $where[] = ['is_delete', '=', Service::DELETE_NO];
        $sort = $request->input('sort_id');
        if ($sort) $where[] = ['sort_id', '=', $sort];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['name', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->tutorialsModel->select('id', 'sort_id', 'name', 'image', 'order', 'status', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $v['image'] = json_decode($v['image'], true) ?? [];
        }
        $data['lists'] = $lists;
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
            'image'   => json_encode($request->input('image'), JSON_UNESCAPED_UNICODE),
            'order'   => $request->input('order', 0),
        ];
        return $this->tutorialsModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'sort_id' => $request->input('sort_id'),
            'name'    => $request->input('name'),
            'image'   => json_encode($request->input('image'), JSON_UNESCAPED_UNICODE),
            'order'   => $request->input('order', 0),
            'status'  => self::DISABLE
        ];
        return $this->tutorialsModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $info = $this->tutorialsModel->whereKey($id)->first();
        $status = $request->input('status');
        if ($status == 1) {
            $this->redis::HSET('tutorials:' . $info['sort_id'], $id, json_encode([
                'id' => $id, 'sort_id' => $info['sort_id'], 'name' => $info['name'], 'image' => $info['image']
            ], JSON_UNESCAPED_UNICODE));
        } else {
            $this->redis::HDEL('tutorials:' . $info['sort_id'], $id);
        }
        return $this->tutorialsModel->whereKey($id)->update(['status' => $status]);
    }
}
