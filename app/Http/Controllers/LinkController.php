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

    }

    public function edit()
    {

    }

    public function del()
    {
        $this->validate([
            'id'   => 'required|integer|gt:0|exists:sw_user,id',
        ], [
            'id'   => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->linkService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
