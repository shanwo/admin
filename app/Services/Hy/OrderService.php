<?php
/**
 * User: Powerless
 * Date: 22/11/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Hy;

use App\Models\Hy\OrderModel;
use App\Services\Service;

class OrderService extends Service
{
    protected $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new OrderModel();
    }

    public function lists($request)
    {
        $where = [];
        $venueId = $request->input('venue_id');
        if ($venueId) $where[] = ['venue_id', '=', $venueId];
        $order = $request->input('order_sn');
        if ($order) $where[] = ['order_sn', '=', $order];
        $third = $request->input('third_sn');
        if ($third) $where[] = ['third_sn', '=', $third];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['user_id', '=', $uid];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->orderModel->select('id', 'user_id', 'order_sn', 'third_sn', 'price', 'pay_price', 'status')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => $v) {
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user, true);
            $v['avatar'] = $info['avatar'] ?? 'https://cdn.shanwo.cc/image/avatar.jpg';
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
        return $info;
    }
}
