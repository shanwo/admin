<?php
/**
 * User: Powerless
 * Date: 21/08/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\OrderModel;
use App\Models\UserModel;

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
        $where[] = ['is_delete', '=', 0];
        $order = $request->input('order_sn');
        if ($order) $where[] = ['o.order_sn', '=', $order];
        $third = $request->input('third_sn');
        if ($third) $where[] = ['o.third_sn', '=', $third];
        $nickname = $request->input('nickname');
        if ($nickname) $where[] = ['u.nickname', 'like', "%$nickname%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->orderModel->from('sw_order as o')
            ->select('o.id', 'u.nickname', 'o.order_sn', 'o.third_sn', 'o.pay_price', 'o.status')
            ->leftJoin('sw_user as u', 'o.user_id', '=', 'u.id')
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
