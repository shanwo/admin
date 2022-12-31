<?php

namespace App\Console\Commands;

use App\Models\Hy\OrderModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class OrderPayTimeout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:order_pay_timeout';

    protected $redis;
    protected $orderModel;
    /**
     * ShortLists constructor.
     * php artisan command:order_pay_timeout
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        parent::__construct();
        $this->redis = $redis;
        $this->orderModel = new OrderModel();
    }

    public function handle()
    {
        $where[] = ['status', '=', $this->orderModel::ORDER_CREATE];
        $where[] = ['created_at', '<', date('Y-m-d H:i:00' ,strtotime('-2 week'))];
        $lists = $this->orderModel->select('id')->where($where)->get()->toArray();
        $ids = array_column($lists,'id');
        return $this->orderModel->whereIn('id',$ids)->update(['status'=> $this->orderModel::ORDER_CANCEL]);
    }

}
