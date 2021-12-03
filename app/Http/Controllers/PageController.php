<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 * Tips: 页面模块
 */

namespace App\Http\Controllers;

use App\Services\PageService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected $pageService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->pageService = new PageService();
    }

    public function lists()
    {
        $this->validate([
            'nickname' => 'nullable|string|max:16',
            'type'     => 'nullable|integer|between:0,3',
            'open'     => 'nullable|integer|between:0,1',
            'keyword'  => 'nullable|string|max:16',
            'order'    => 'nullable|string|in:follow,browse',
            'page'     => 'filled|integer|gt:0',
            'size'     => 'filled|integer|between:10,100',
        ], [
            'nickname' => '昵称',
            'open'     => '广场',
            'type'     => '用户类型',
            'keyword'  => '关键字',
            'order'    => '排序',
            'page'     => '页码',
            'size'     => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id'             => 'required|integer|gt:0|exists:sw_page,id',
            'browse_page'    => 'filled|integer|gt:0',
            'attention_page' => 'filled|integer|gt:0',
            'size'           => 'filled|integer|between:10,100',
        ], [
            'id'             => 'ID',
            'browse_page'    => '浏览页码',
            'attention_page' => '关注页码',
            'size'           => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function sort()
    {
        $this->validate([
            'id'    => 'required|integer|gt:0|exists:sw_page,id',
            'sort'  => 'required|integer|between:-8388608,8388607',
            'cache' => 'nullable|integer|between:0,3',
        ], [
            'id'    => 'ID',
            'sort'  => '排序值',
            'cache' => '缓存',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->sort($this->request);
        if (!empty($data['msg'])) {
            $this->msg = $data['msg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_page,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
