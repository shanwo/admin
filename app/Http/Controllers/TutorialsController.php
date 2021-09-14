<?php
/**
 * User: Powerless
 * Date: 2021/7/18
 * Blog: https://wzxaini9.cn
 * Tips: 教程模块
 */

namespace App\Http\Controllers;

use App\Services\SortService;
use App\Services\TutorialsService;
use Illuminate\Http\Request;

class TutorialsController extends Controller
{
    protected $sortService;
    protected $tutorialsService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortService = new SortService();
        $this->tutorialsService = new TutorialsService();
    }

    public function lists()
    {
        $this->validate([
            'sort_id' => 'nullable|integer|gt:0|exists:sw_sort,id',
        ], [
            'sort_id' => '分类ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data['lists'] = $this->tutorialsService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_tutorials,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tutorialsService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'sort_id' => 'required|integer|gt:0|exists:sw_sort,id',
            'name'    => 'required|string|min:2|max:32',
            'image'   => 'required|url|max:128',
            'order'   => 'nullable|integer|between:0,255',
        ], [
            'sort_id' => '分类ID',
            'name'    => '名称',
            'image'   => '图片',
            'order'   => '排序',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tutorialsService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'      => 'required|integer|gt:0|exists:sw_tutorials,id',
            'sort_id' => 'required|integer|gt:0|exists:sw_sort,id',
            'name'    => 'required|string|min:2|max:32',
            'image'   => 'required|url|max:128',
            'order'   => 'nullable|integer|between:0,255',
        ], [
            'id' => 'ID',
            'sort_id' => '分类ID',
            'name'    => '名称',
            'image'   => '图片',
            'order'   => '排序',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tutorialsService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_tutorials,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->tutorialsService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
