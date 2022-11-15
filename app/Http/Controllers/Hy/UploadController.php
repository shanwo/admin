<?php

namespace App\Http\Controllers\Hy;

use App\Http\Controllers\Controller;
use App\Services\Hy\UploadService;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    protected $payment;
    protected $uploadService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->uploadService = new UploadService();
    }

    public function lists()
    {
        $this->validate([
            'venue_id'   => 'nullable|integer|gt:0',
            'user_id'    => 'nullable|integer|gt:0',
            'keyword'    => 'nullable|string|max:32',
            'start_time' => 'filled|date',
            'end_time'   => 'filled|date|after_or_equal:start_time',
            'page'       => 'filled|integer|gt:0',
            'size'       => 'filled|integer|between:10,100',
        ], [
            'venue_id'   => '场地ID',
            'user_id'    => '用户ID',
            'keyword'    => '文件名称',
            'start_time' => '开始时间',
            'end_time'   => '结束时间',
            'page'       => '页码',
            'size'       => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->uploadService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id' => 'required|integer|gt:0',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->uploadService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function clean()
    {
        $this->validate([
            'venue_id' => 'required|integer|gt:0',
        ], [
            'venue_id' => '场地ID',
        ]);
        $data = $this->uploadService->clean($this->request);
        if (!empty($data['msg'])) {
            $this->msg = $data['msg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function report()
    {
        $this->validate([
            'venue_id' => 'required|integer|gt:0',
            'images'   => 'required|array|min:1|max:10',
        ], [
            'venue_id' => '场地ID',
            'images'   => '图片数组',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->uploadService->report($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
