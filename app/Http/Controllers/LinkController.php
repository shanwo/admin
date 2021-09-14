<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 * Tips: 链接模块
 */

namespace App\Http\Controllers;

use App\Services\LinkService;
use App\Services\SortService;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    protected $sortService;
    protected $linkService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortService = new SortService();
        $this->linkService = new LinkService();
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
        $data = $this->linkService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_link,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->linkService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'sort_id'     => 'required|integer|gt:0|exists:sw_sort,id',
            'name'        => 'required|string|min:2|max:32',
            'image'       => 'required|url|max:128',
            'svg'         => 'nullable|url|max:128',
            'url'         => 'required|url|max:128',
            'order'       => 'nullable|integer|max:128',
            'description' => 'nullable|url|max:128',
            'type'        => 'required|integer|between:1,2',
        ], [
            'sort_id'     => '分类ID',
            'name'        => '名称',
            'image'       => '图片',
            'svg'         => '矢量图',
            'url'         => '链接',
            'order'       => '排序',
            'description' => '说明',
            'type'        => '类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->linkService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'          => 'required|integer|gt:0|exists:sw_link,id',
            'sort_id'     => 'required|integer|gt:0|exists:sw_sort,id',
            'name'        => 'required|string|min:2|max:32',
            'image'       => 'required|url|max:128',
            'url'         => 'required|url|max:128',
            'svg'         => 'nullable|url|max:128',
            'order'       => 'nullable|integer|max:128',
            'description' => 'nullable|url|max:128',
            'type'        => 'required|integer|between:1,2',
        ], [
            'id'          => 'ID',
            'sort_id'     => '分类ID',
            'name'        => '名称',
            'image'       => '图片',
            'svg'         => '矢量图',
            'url'         => '链接',
            'order'       => '排序',
            'description' => '说明',
            'type'        => '类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->linkService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id'          => 'required|integer|gt:0|exists:sw_link,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->linkService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
