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
            'type' => 'nullable|integer|between:0,4',
        ], [
            'type' => '类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->sortService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'name'        => 'required|string|min:2|max:32',
            'type'        => 'required|integer|between:1,4',
            'order'       => 'nullable|integer|between:0,255',
        ], [
            'name'        => '名称',
            'type'        => '类型',
            'order'       => '排序',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->sortService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id' => 'required|integer|gt:0',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->sortService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
