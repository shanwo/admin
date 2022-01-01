<?php

namespace App\Http\Controllers;

use App\Services\ReplyService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    protected $official;
    protected $replyService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->replyService = new ReplyService();
        $config = $this->service->configInfo('official');
        $this->official = Factory::officialAccount($config);
    }

    public function menu()
    {
        $data = $this->official->menu->list();
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function setMenu()
    {
        $this->validate([
            'menu'       => 'required|array',
        ], [
            'menu'    => '菜单',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = [];
        $menu = $this->request->input('menu');
        if(array_key_exists('menu',$menu)){
            if(array_key_exists('button',$menu['menu'])){
                $this->code = 200;
                $data = $this->official->menu->create($menu['menu']['button']);
            }else{
                $this->msg = '菜单按钮不可为空';
            }
        }else{
            $this->msg = '默认菜单不可为空';
        }
        return $this->returnApi($data);
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