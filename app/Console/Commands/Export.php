<?php

namespace App\Console\Commands;

use App\Models\Hy\ExportModel;
use App\Models\Hy\OrderModel;
use Illuminate\Console\Command;

class Export extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:export';

    protected $exportModel;
    /**
     * ShortLists constructor.
     * php artisan command:short_lists
     */
    public function __construct()
    {
        parent::__construct();
        $this->exportModel = new ExportModel();
    }

    public function handle()
    {
        $where[] = ['status', '=', $this->exportModel::EXPORT_CREATE];
        $lists = $this->exportModel->select('id', 'type', 'data')->where($where)->get()->toArray();
        foreach ($lists as $k => $v) {
            $this->exportModel->whereKey('id', $v['id'])->update(['status' => $this->exportModel::EXPORT_EXECUTION]);
            try {
                $data = json_decode($v['data'], true) ?? [];
                switch ($v['type']) {
                    case 1:
                        $update = $this->order($v['id'], $data);
                        break;
                    default:
                        $update = ['status' => $this->exportModel::EXPORT_CANCEL];
                }
            } catch (\Exception $e) {
                $update = ['data'=>$e->getMessage(),'status' => $this->exportModel::EXPORT_FAIL];
            }
            $this->exportModel->whereKey('id', $v['id'])->update($update);
        }
    }

    private function order($id,$data)
    {
        $orderModel = new OrderModel();
        $where = [];
        if(!empty($data['venue_id'])) $where[] = ['o.venue_id', '=', $data['venue_id']];
        if(!empty($data['order_sn'])) $where[] = ['o.order_sn', '=', $data['order_sn']];
        if(!empty($data['third_sn'])) $where[] = ['o.third_sn', '=', $data['third_sn']];
        if(!empty($data['user_id'])) $where[] = ['o.user_id', '=', $data['user_id']];
        if(!empty($data['status'])) $where[] = ['o.status', '=', $data['status']];
        if(!empty($data['start_time'])) $where[] = ['o.payated_at', '>=', $data['start_time'] . ' 00:00:00'];
        if(!empty($data['end_time'])) $where[] = ['o.payated_at', '<=', $data['end_time']  . ' 00:00:00'];
        $lists = $orderModel->from('hy_order as o')
            ->select('o.id', 'venue.name as venue_name', 'o.user_id', 'o.order_sn', 'o.third_sn', 'o.price', 'o.pay_price', 'o.status', 'o.payated_at')
            ->leftjoin('hy_venue as venue', 'venue.id', '=', 'o.venue_id')->where($where)->get()->toArray();
        return (new InvoicesExport)->download('invoices.xlsx', \Maatwebsite\Excel\Excel::XLSX);

        $cellData = [
            ['台班签证单', '', '', ''],
            ['时间段', '开始时间', '截止时间', '备注'],
            ['上午', '8时30分钟', '11时00分钟', '无'],
            ['', '11时00分钟', '14时00分钟', ''],
            ['注: 此表24小时内审批签字 否则无效', '', '', ''],
        ];
        Excel::create('台班签证单', function ($excel) use ($cellData) {
            $excel->sheet('第一页', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xlsx');
        return['file_name'=>'','file_url'=>'','status' => $this->exportModel::EXPORT_FAIL];
    }


}
