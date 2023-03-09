<?php

namespace App\Console\Commands;

use App\Models\Hy\OrderModel;
use Illuminate\Console\Command;

class OrderPayTimeout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:order_pay_timeout';

    protected $orderModel;

    /**
     * ShortLists constructor.
     * php artisan command:order_pay_timeout
     */
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new OrderModel();
    }

    public function handle()
    {
        $where[] = ['status', '=', $this->orderModel::ORDER_CREATE];
        $where[] = ['created_at', '<', date('Y-m-d H:i:00', strtotime('-2 week'))];
        $lists = $this->orderModel->select('id')->where($where)->get()->toArray();
        if ($lists) {
            $ids = array_column($lists, 'id');
            echo "\r\n【开始关闭订单】" . implode(',', $ids);
            $this->orderModel->whereIn('id', $ids)->update(['status' => $this->orderModel::ORDER_CANCEL]);
            echo "\r\n【结束关闭订单】" . implode(',', $ids);
        }
        return $lists;
    }

}
