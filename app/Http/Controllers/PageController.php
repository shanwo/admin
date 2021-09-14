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
            'type'     => 'nullable|integer|between:0,1',
            'open'     => 'nullable|integer|between:0,1',
            'keyword'  => 'nullable|string|max:16',
            'page'     => 'filled|integer|gt:0',
            'size'     => 'filled|integer|between:10,100',
        ], [
            'nickname' => '昵称',
            'open'     => '广场',
            'type'     => '用户类型',
            'keyword'  => '关键字',
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

    public function sort()
    {
        $this->validate([
            'id'     => 'required|integer|gt:0',
            'sort'   => 'required|integer|between:-8388608,8388607',
        ], [
            'id'     => 'ID',
            'sort'   => '排序值',
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
            'id'     => 'required|integer|gt:0',
        ], [
            'id'     => 'ID',
            'sort'   => '排序值',
            'delete' => '删除',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
