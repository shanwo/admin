<?php

namespace App\Console\Commands\Export;

use App\Models\Hy\OrderModel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Order implements FromArray, WithHeadings
{
    protected $data;

    //构造函数传值
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return ["场地名称", "用户ID", "订单号", "三方单号", "单价（元）", "支付（元）", "订单状态", "支付时间"];
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $where = [];
        if (!empty($this->data['venue_id'])) $where[] = ['o.venue_id', '=', $this->data['venue_id']];
        if (!empty($this->data['order_sn'])) $where[] = ['o.order_sn', '=', $this->data['order_sn']];
        if (!empty($this->data['third_sn'])) $where[] = ['o.third_sn', '=', $this->data['third_sn']];
        if (!empty($this->data['user_id'])) $where[] = ['o.user_id', '=', $this->data['user_id']];
        if (!empty($this->data['status'])) $where[] = ['o.status', '=', $this->data['status']];
        if (!empty($this->data['start_time'])) $where[] = ['o.payated_at', '>=', $this->data['start_time'] . ' 00:00:00'];
        if (!empty($this->data['end_time'])) $where[] = ['o.payated_at', '<=', $this->data['end_time'] . ' 00:00:00'];
        $lists = (new OrderModel)->from('hy_order as o')
            ->select('venue.name as venue_name', 'o.user_id', 'o.order_sn', 'o.third_sn', 'o.price', 'o.pay_price', 'o.status', 'o.payated_at')
            ->leftjoin('hy_venue as venue', 'venue.id', '=', 'o.venue_id')->where($where)->get()->toArray();
        foreach ($lists as &$v) {
            $v['price'] = bcdiv($v['price'], 100, 2);
            $v['pay_price'] = bcdiv($v['pay_price'], 100, 2);
        }
        return $lists;
    }
}
