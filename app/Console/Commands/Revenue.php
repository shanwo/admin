<?php
/**
 * User: Powerless
 * Date: 23/04/29
 * Blog: https://wzxaini9.cn
 */

namespace App\Console\Commands;

use App\Models\Hy\OrderModel;
use App\Models\Hy\RevenueModel;
use App\Models\Hy\UploadModel;
use Illuminate\Console\Command;

class Revenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:revenue';
    protected $orderModel;
    protected $uploadModel;
    protected $revenueModel;


    /**
     * ShortLists constructor.
     * php artisan command:revenue
     */
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new OrderModel();
        $this->uploadModel = new UploadModel();
        $this->revenueModel = new RevenueModel();
    }

    public function handle()
    {
        $statsTime = date('Y-m-d', strtotime("-1 day"));
        $this->statistics($statsTime);
    }

    private function statistics($statsTime)
    {
        $inserts = [];
        $where[] = ['status', '=', $this->orderModel::ORDER_SUCCESS];
        $where[] = ['payated_at', '>', $statsTime . ' 00:00:00'];
        $where[] = ['payated_at', '<', $statsTime . ' 23:59:59'];
        $ordersRaw = $this->orderModel->select('id', 'original_ids', 'order_sn', 'price')->where($where)->get()->toArray();
        foreach ($ordersRaw as $order) {
            $ids = json_decode($order['original_ids'], true);
            $imagesRaw = $this->uploadModel->select('id', 'venue_id', 'user_id')->whereKey($ids)->get()->toArray();
            foreach ($imagesRaw as $image) {
                $key = $image['venue_id'] . '-' . $image['user_id'];
                if (array_key_exists($key, $inserts)) {
                    if (!in_array($order['order_sn'], $inserts[$key]['order_sns'])) $inserts[$key]['order_sns'][] = $order['order_sn'];
                    $inserts[$key]['number']++;
                    $inserts[$key]['receivables'] += $order['price'];
                } else {
                    $inserts[$key] = [
                        'venue_id'    => $image['venue_id'],
                        'user_id'     => $image['user_id'],
                        'stats_time'  => $statsTime,
                        'order_sns'   => [$order['order_sn']],
                        'price'       => $order['price'],
                        'number'      => 1,
                        'receivables' => $order['price']
                    ];
                }
            }
        }
        $this->add($inserts);
    }

    private function add($inserts)
    {
        foreach ($inserts as &$insert) {
            $insert['order_sns'] = json_encode($insert['order_sns']);
        }
        $this->revenueModel->insert($inserts);
    }
}