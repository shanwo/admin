<?php
/**
 * User: Powerless
 * Date: 22/11/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Hy;

use App\Models\Hy\VenueModel;
use App\Services\Service;

class VenueService extends Service
{
    protected $venueModel;

    public function __construct()
    {
        parent::__construct();
        $this->venueModel = new VenueModel();
    }

    public function lists($request)
    {
        $where[] = ['status', '=', Service::DELETE_NO];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['name', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->venueModel->select('id', 'name', 'price', 'merger_name', 'address', 'status', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        return $this->venueModel->whereKey($id)->first();
    }

    public function add($request)
    {
        $insert = [
            'name'      => $request->input('name'),
            'image'     => $request->input('image'),
            'price'     => $request->input('price'),
            'area_id'   => $request->input('area_id'),
            'address'   => $request->input('address'),
            'sub_mchid' => $request->input('sub_mchid', 0),
        ];
        return $this->venueModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'name'      => $request->input('name'),
            'image'     => $request->input('image'),
            'price'     => $request->input('price'),
            'area_id'   => $request->input('area_id'),
            'address'   => $request->input('address'),
            'sub_mchid' => $request->input('sub_mchid', 0),
        ];
        return $this->venueModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $this->cache($request);
        return $this->venueModel->whereKey($id)->update(['status' => $status]);
    }

    public function cache($request)
    {
        $this->redis::HDEL('link:' . $info['sort_id'], $id);
        $this->redis::HSET('link:' . $info['sort_id'], $id, json_encode([
            'id' => $id, 'sort_id' => $info['sort_id'], 'name' => $info['name'], 'image' => $info['image'], 'svg' => $info['svg'] ?? '', 'url' => $info['url']
        ], JSON_UNESCAPED_UNICODE));
    }
}
