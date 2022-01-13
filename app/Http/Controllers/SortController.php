<?php
/**
 * User: Powerless
 * Date: 21/09/13
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\SortService;
use Illuminate\Http\Request;

class SortController extends Controller
{
    protected $sortService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortService = new SortService();
    }

    public function lists()
    {
        $this->validate([
            'keyword' => 'nullable|string|max:32',
            'type'    => 'nullable|integer|between:0,4',
        ], [
            'keyword' => '搜索内容',
            'type'    => '类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->sortService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_sort,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->sortService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'name'  => 'required|string|min:2|max:32',
            'type'  => 'required|integer|between:1,4',
            'order' => 'nullable|integer|between:0,255',
        ], [
            'name'  => '名称',
            'type'  => '类型',
            'order' => '排序',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->sortService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'    => 'required|integer|gt:0|exists:sw_sort,id',
            'name'  => 'required|string|min:2|max:32',
            'order' => 'nullable|integer|between:0,255',
        ], [
            'id'    => 'ID',
            'name'  => '名称',
            'order' => '排序',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->sortService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_sort,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $info = $this->sortService->info($this->request);
        $count = $this->sortService->checkTotal($info);
        if ($count) {
            $this->msg = '当前分类下存在' . $count . '条数据，不可删除';
            return $this->returnApi();
        }
        $data = $this->sortService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
