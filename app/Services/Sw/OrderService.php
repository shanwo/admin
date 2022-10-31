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
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->orderModel->from('sw_order as o')
            ->select('id', 'user_id', 'order_sn', 'third_sn', 'pay_price', 'status')
            ->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($where)
    {
        return $this->orderModel->where($where)->first();
    }
}
