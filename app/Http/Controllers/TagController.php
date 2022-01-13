<?php

namespace App\Http\Controllers;

use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    protected $tagService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->tagService = new TagService();
    }

    public function lists()
    {
        $this->validate([
            'keyword' => 'nullable|string|max:32',
            'type'    => 'nullable|integer|between:0,5',
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'keyword' => '搜索内容',
            'type'    => '类型',
            'page'    => '页码',
            'size'    => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tagService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_tag,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tagService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'name'    => 'required|string|min:2|max:32',
            'mark'    => 'required|string|min:2|max:32',
            'type'    => 'required|integer|between:1,5',
            'default' => 'nullable|string|max:128',
        ], [
            'name'    => '名称',
            'mark'    => '标识',
            'type'    => '类型',
            'default' => '默认值',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tagService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'      => 'required|integer|gt:0|exists:sw_tag,id',
            'name'    => 'required|string|min:2|max:32',
            'mark'    => 'required|string|min:2|max:32',
            'type'    => 'required|integer|between:1,5',
            'default' => 'nullable|string|max:128',
        ], [
            'id'      => 'ID',
            'name'    => '名称',
            'mark'    => '标识',
            'type'    => '类型',
            'default' => '默认值',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tagService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'     => 'required|integer|gt:0|exists:sw_tag,id',
            'status' => 'required|integer|in:-1,1',
        ], [
            'id'     => 'ID',
            'status' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tagService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}