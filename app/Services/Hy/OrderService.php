<?php
/**
 * User: Powerless
 * Date: 22/11/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Hy;

use App\Models\Hy\OrderModel;
use App\Models\Hy\UploadModel;
use App\Services\Service;

class OrderService extends Service
{
    protected $orderModel;
    protected $uploadModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new OrderModel();
        $this->uploadModel = new UploadModel();
    }

    public function lists($request)
    {
        $where = [];
        $venueId = $request->input('venue_id');
        if ($venueId) $where[] = ['o.venue_id', '=', $venueId];
        $order = $request->input('order_sn');
        if ($order) $where[] = ['o.order_sn', '=', $order];
        $third = $request->input('third_sn');
        if ($third) $where[] = ['o.third_sn', '=', $third];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['o.user_id', '=', $uid];
        $status = $request->input('status');
        if ($status) $where[] = ['o.status', '=', $status];
        $start_time = $request->input('start_time');
        if ($start_time) $where[] = ['o.payated_at', '>=', $start_time . ' 00:00:00'];
        $end_time = $request->input('end_time');
        if ($end_time) $where[] = ['o.payated_at', '<=', $end_time . ' 23:59:59'];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->orderModel->from('hy_order as o')
            ->select('o.id', 'venue.name as venue_name', 'o.user_id', 'o.order_sn', 'o.third_sn', 'o.price', 'o.pay_price', 'o.status', 'o.payated_at')
            ->leftjoin('hy_venue as venue', 'venue.id', '=', 'o.venue_id')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('o.id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => $v) {
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user, true);
            $v['nickname'] = $info['nickname'] ?? '';
            $v['price'] = bcdiv($v['price'], 100, 2);
            $v['pay_price'] = bcdiv($v['pay_price'], 100, 2);
            $data['lists'][] = $v;
        }
        return $data;
    }

    public function info($where)
    {
        $info = $this->orderModel->where($where)->first();
        $info['price'] = bcdiv($info['price'], 100, 2);
        $info['pay_price'] = bcdiv($info['pay_price'], 100, 2);
        $ids = json_decode($info['original_ids'], true);
        $info['images'] = $this->uploadModel->select('id', 'thumbnail_url')->whereIn('id', $ids)->get()->toArray();
        return $info;
    }
}
