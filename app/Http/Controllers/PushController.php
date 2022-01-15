<?php

namespace App\Http\Controllers;

use App\Services\PushService;
use Illuminate\Http\Request;

class PushController extends Controller
{
    protected $pushService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->pushService = new PushService();
    }

    public function lists()
    {
        $this->validate([
            'keyword'    => 'nullable|string|max:32',
            'start_type' => 'required|integer|between:0,3',
            'page'       => 'filled|integer|gt:0',
            'size'       => 'filled|integer|between:10,100',
        ], [
            'keyword'    => '搜索内容',
            'start_type' => '推送方式',
            'page'       => '页码',
            'size'       => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pushService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_push,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pushService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'name'         => 'required|string|min:2|max:32',
            'start_type'   => 'required|integer|between:1,3',
            'start_method' => 'required_unless:start_type,1|integer|between:0,5',
            'start_time'   => 'required_unless:start_type,1|string|min:5|max:10',
            'rule'         => 'required|array',
            'config'       => 'required|array',
        ], [
            'name'         => '名称',
            'start_type'   => '推送方式',
            'start_method' => '推送模式',
            'start_time'   => '推送时间',
            'rule'         => '规则',
            'config'       => '配置',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pushService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'           => 'required|integer|gt:0|exists:sw_push,id',
            'name'         => 'required|string|min:2|max:32',
            'start_type'   => 'required|integer|between:1,3',
            'start_method' => 'required_unless:start_type,1|integer|between:0,5',
            'start_time'   => 'required_unless:start_type,1|string|min:5|max:10',
            'rule'         => 'required|array',
            'config'       => 'required|array',
        ], [
            'id'           => 'ID',
            'name'         => '名称',
            'start_type'   => '推送方式',
            'start_method' => '推送模式',
            'start_time'   => '推送时间',
            'rule'         => '规则',
            'config'       => '配置',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pushService->edit($this->request);
        if (!empty($data['msg'])) {
            $this->msg = $data['msg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'     => 'required|integer|gt:0|exists:sw_push,id',
            'status' => 'required|integer|in:-1,1,2',
        ], [
            'id'     => 'ID',
            'status' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pushService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}