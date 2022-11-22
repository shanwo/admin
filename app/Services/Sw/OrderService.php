<?php
/**
 * User: Powerless
 * Date: 21/08/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Sw;

use App\Models\Sw\OrderModel;
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
        $order = $request->input('order_sn');
        if ($order) $where[] = ['order_sn', '=', $order];
        $third = $request->input('third_sn');
        if ($third) $where[] = ['third_sn', '=', $third];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['user_id', '=', $uid];
        $status = $request->input('status');
        if ($status) $where[] = ['status', '=', $status];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->orderModel->select('id', 'user_id', 'order_sn', 'third_sn', 'pay_price', 'status', 'payated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => $v) {
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user, true);
            $v['nickname'] = $info['nickname'] ?? '';
            $v['pay_price'] = bcdiv($v['pay_price'], 100, 2);
            $data['lists'][] = $v;
        }
        return $data;
    }
}
