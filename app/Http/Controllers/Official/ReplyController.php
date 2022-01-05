<?php

namespace App\Http\Controllers\Official;

use App\Http\Controllers\Controller;
use App\Services\ReplyService;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    protected $replyService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->replyService = new ReplyService();
    }

    public function lists()
    {
        $this->validate([
            'type'    => 'nullable|integer|between:0,6',
            'keyword' => 'nullable|string|max:16',
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'type'    => '类型',
            'keyword' => '关键字',
            'page'    => '页码',
            'size'    => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->replyService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_reply,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->replyService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'type'    => 'required|integer|between:1,6',
            'keyword' => 'required|string|min:2|max:32',
            'comment' => 'required|array',
        ], [
            'type'    => '类型',
            'keyword' => '关键字',
            'comment' => '内容',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->replyService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'      => 'required|integer|gt:0|exists:sw_reply,id',
            'type'    => 'required|integer|between:1,6',
            'keyword' => 'required|string|min:2|max:32',
            'comment' => 'required|array',
        ], [
            'id'      => 'ID',
            'type'    => '类型',
            'keyword' => '关键字',
            'comment' => '内容',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->replyService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'     => 'required|integer|gt:0|exists:sw_reply,id',
            'status' => 'required|integer|in:-1,1',
        ], [
            'id'     => 'ID',
            'status' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->replyService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}