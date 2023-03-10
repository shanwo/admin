<?php
/**
 * User: Powerless
 * Date: 22/11/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Hy;

use App\Models\AreaModel;
use App\Models\Hy\UploadModel;
use App\Models\Hy\VenueModel;
use App\Services\Service;

class VenueService extends Service
{
    protected $areaModel;
    protected $venueModel;
    protected $uploadModel;

    public function __construct()
    {
        parent::__construct();
        $this->areaModel = new AreaModel();
        $this->venueModel = new VenueModel();
        $this->uploadModel = new UploadModel();
    }

    public function select()
    {
        $data['lists'] = $this->venueModel->select('id', 'name')->where(['status' => Service::ENABLE])->get()->toArray();
        return $data;
    }

    public function lists($request)
    {
        $where[] = ['status', '<>', Service::DELETE];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['name', 'like', "%$keyword%"];
        $status = $request->input('status');
        if ($status) $where[] = ['status', '=', $status];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->venueModel->select('id', 'name', 'price', 'merger_name', 'address', 'status', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as &$v) {
            $v['price'] = bcdiv($v['price'], 100, 2);
        }
        $data['lists'] = $lists;
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->venueModel->whereKey($id)->first();
        $info['price'] = $info['price'] / 100;
        $area = $this->areaModel->whereKey($info['area_id'])->first();
        $info['city_id'] = $area['pid'];
        $area = $this->areaModel->whereKey($area['pid'])->first();
        $info['province_id'] = $area['pid'];
        return $info;
    }

    public function add($request)
    {
        $aid = $request->input('area_id');
        $info = $this->areaModel->whereKey($aid)->first();
        $price = $request->input('price');
        $price = bcmul($price, 100);
        $insert = [
            'name'        => $request->input('name'),
            'image'       => $request->input('image'),
            'price'       => $price,
            'area_id'     => $info['id'],
            'merger_name' => $info['merger_name'],
            'address'     => $request->input('address'),
            'sub_mchid'   => $request->input('sub_mchid'),
            'status'      => Service::DISABLE,
        ];
        return $this->venueModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $aid = $request->input('area_id');
        $info = $this->areaModel->whereKey($aid)->first();
        $price = $request->input('price');
        $price = bcmul($price, 100);
        $update = [
            'name'        => $request->input('name'),
            'image'       => $request->input('image'),
            'price'       => $price,
            'area_id'     => $info['id'],
            'merger_name' => $info['merger_name'],
            'address'     => $request->input('address'),
            'status'      => Service::DISABLE,
        ];
        $this->cache($id, Service::DISABLE);
        return $this->venueModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $this->cache($id, $status);
        return $this->venueModel->whereKey($id)->update(['status' => $status]);
    }

    public function cache($id, $status = Service::ENABLE)
    {
        $del = $this->redis::LRANGE('venue_days:' . $id, 0, -1);
        foreach ($del as $day) {
            $hour = $this->redis::LRANGE('venue_hours:' . $id . ':' . $day, 0, -1);
            foreach ($hour as $h) {
                $this->redis::DEL('venue_images:' . $id . ':' . $day . ':' . $h);
            }
            $this->redis::DEL('venue_hours:' . $id . ':' . $day);
        }
        $this->redis::DEL('venue_days:' . $id);
        $this->redis::HDEL('venues', $id);
        if ($status == Service::ENABLE) {
            $info = $this->venueModel->select('id', 'name', 'price', 'image', 'sub_mchid', 'merger_name', 'address')->whereKey($id)->first();
            $this->redis::HSET('venues', $id, json_encode($info, JSON_UNESCAPED_UNICODE));
            $images = $this->uploadModel->where([['venue_id', '=', $id], ['status', '<>', self::DELETE]])
                ->whereDate('updated_at', '>', date('Y-m-d', strtotime('-3 month')))->orderBy('created_at', 'ASC')->get()->toArray();
            if (!$images) return ['msg' => '当前场地近3个月未查询到照片数据'];
            foreach ($images as $k => $v) {
                [$day, $hour, $score] = $this->score($v['created_at']);
                $rKey = $id . ':' . $day;
                if (!$this->redis::EXISTS('venue_images:' . $rKey . ':' . $hour)) {
                    $hours = $this->redis::LRANGE('venue_hours:' . $rKey, 0, -1);
                    if (!in_array($hour, $hours)) {
                        $this->redis::LPUSH('venue_hours:' . $rKey, $hour);
                        $days = $this->redis::LRANGE('venue_days:' . $id, 0, -1);
                        if (!in_array($day, $days)) $this->redis::LPUSH('venue_days:' . $id, $day);
                    }
                }
                $img = [
                    'id'   => $v['id'],
                    'name' => $v['name'],
                    'url'  => $v['thumbnail_url']
                ];
                $this->redis::ZADD('venue_images:' . $rKey . ':' . $hour, $score, json_encode($img));
            }
            $this->redis::SETEX('set_images', 900, date('Y-m-d H:i:s'));
        }
        return [];
    }
}
