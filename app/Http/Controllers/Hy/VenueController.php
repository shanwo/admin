<?php

namespace App\Http\Controllers\Hy;

use App\Http\Controllers\Controller;
use App\Services\Hy\VenueService;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    protected $venueService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->venueService = new VenueService();
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
        $data = $this->venueService->lists($this->request);
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
        $data = $this->venueService->info($this->request);
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
        $data = $this->venueService->add($this->request);
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
        $data = $this->venueService->edit($this->request);
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
        $data = $this->venueService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
