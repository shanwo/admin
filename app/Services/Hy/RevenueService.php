<?php
/**
 * User: Powerless
 * Date: 23/04/29
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Hy;

use App\Models\Hy\OrderModel;
use App\Models\Hy\RevenueModel;
use App\Services\Service;

class RevenueService extends Service
{
    protected $orderModel;
    protected $revenueModel;
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new OrderModel();
        $this->revenueModel = new RevenueModel();
    }

    public function lists($request)
    {
        $vid = $request->input('venue_id');
        $where[] = ['r.venue_id', '=', $vid];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['r.user_id', '=', $uid];
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        if ($start_time && $end_time) {
            $where[] = ['r.stats_time', '>=', $start_time];
            $where[] = ['r.stats_time', '<=', $end_time];
        }
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->revenueModel->from('hy_revenue as r')
            ->select('r.id', 'venue.name as venue_name', 'r.user_id',  'r.price', 'r.receivables', 'r.stats_time')
            ->leftjoin('hy_venue as venue', 'venue.id', '=', 'r.venue_id')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as &$v) {
            $v['price'] = bcdiv($v['price'], 100, 2);
            $v['receivables'] = bcdiv($v['receivables'], 100, 2);
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user, true);
            $v['nickname'] = $info['nickname'] ?? '';
        }
        $data['lists'] = $lists;
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->revenueModel->whereKey($id)->first();
        $orderSns = json_decode($info['order_sns'], true);
        $lists = $this->orderModel
            ->select('user_id', 'order_sn', 'pay_price', 'payated_at')
            ->whereIn('order_sn', $orderSns)->get()->toArray();
        foreach ($lists as &$v) {
            $v['pay_price'] = bcdiv($v['pay_price'], 100, 2);
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user, true);
            $v['nickname'] = $info['nickname'] ?? '';
        }
        return ['lists' => $lists];
    }
}
