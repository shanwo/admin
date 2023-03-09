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
        $where[] = ['status', '=', $this->exportModel::EXPORT_CREATE];
        $lists = $this->exportModel->select('id', 'type', 'data')->where($where)->get()->toArray();
        foreach ($lists as $k => $v) {
            echo "\r\n【开始执行任务】" . $v['id'];
            $this->exportModel->whereKey('id', $v['id'])->update(['status' => $this->exportModel::EXPORT_EXECUTION]);
            try {
                $data = json_decode($v['data'], true) ?? [];
                switch ($v['type']) {
                    case 1:
                        $update = $this->order($data);
                        break;
                    default:
                        $update = ['status' => $this->exportModel::EXPORT_CANCEL];
                }
            } catch (\Exception $e) {
                $update = ['data' => ['file' => $e->getFile(), 'line' => $e->getLine(), 'message' => $e->getMessage()], 'status' => $this->exportModel::EXPORT_FAIL];
            }
            if (!empty($update['file'])) {
                $oss = $this->uploadService->local($update['file']);
                $update['file_url'] = $oss['url'];
                $update['file_name'] = $oss['name'];
                @unlink($update['file']);
                unset($update['file']);
            }
            $this->exportModel->whereKey($v['id'])->update($update);
            echo "\r\n【结束执行任务】" . $v['id'];
        }
    }

    private function order($data)
    {
        $name = date('Ymd-His') . '.xlsx';
        Excel::store(new Order($data), $name);
        $local = (env('APP_STORAGE') ?? storage_path('app')) . DIRECTORY_SEPARATOR . $name;
        return ['file' => $local, 'status' => $this->exportModel::EXPORT_SUCCESS];
    }


}
