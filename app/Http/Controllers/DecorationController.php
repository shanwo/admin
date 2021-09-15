<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 * Tips: 装饰模块
 */

namespace App\Http\Controllers;

use App\Services\DecorationService;
use Illuminate\Http\Request;

class DecorationController extends Controller
{
    protected $decorationService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->decorationService = new DecorationService();
    }

    public function lists()
    {
        $this->validate([
            'sort_id' => 'nullable|integer|gt:0|exists:sw_sort,id',
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'sort_id' => '分类ID',
            'page'    => '页码',
            'size'    => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data['lists'] = $this->decorationService->lists($this->request);
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
        $data = $this->decorationService->add($this->request);
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
            'id'      => 'ID',
            'sort_id' => '分类ID',
            'name'    => '名称',
            'image'   => '图片',
            'order'   => '排序',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->decorationService->edit($this->request);
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
        $data = $this->decorationService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
