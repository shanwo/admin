<?php

namespace App\Console\Commands;

use App\Console\Commands\Export\Order;
use App\Models\Hy\ExportModel;
use App\Services\UploadService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class Export extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:export';

    protected $exportModel;
    protected $uploadService;
    /**
     * ShortLists constructor.
     * php artisan command:export
     */
    public function __construct()
    {
        parent::__construct();
        $this->exportModel = new ExportModel();
        $this->uploadService = new UploadService();
    }

    public function handle()
    {
        $where = [];
//        $where[] = ['status', '=', $this->exportModel::EXPORT_CREATE];
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
            var_dump($update);die;
            if (!empty($update['file'])) {
                $file = [
                    'name'     => 'file',
                    'contents' => fopen($update['file'], 'r'),
                    'headers'  => [
                        'content-type' => 'image/jpg'
                    ]];
                $oss = $this->uploadService->update($file);
                $update['file_url'] = $oss['url'];
                $update['file_name'] = $oss['name'];
                @unlink($file['file']);
                unset($file['file']);
            }
            $this->exportModel->whereKey('id', $v['id'])->update($update);
        }
    }

    private function order($id,$data)
    {
        $name = date('Ymd-His').'_order.xlsx';
        Excel::store(new Order($data), $name);
        $local = (env('APP_STORAGE') ?? storage_path('app')) . DIRECTORY_SEPARATOR . $name;
        return['file_name'=>$name,'file_url'=>$local,'status' => $this->exportModel::EXPORT_SUCCESS];
    }


}
