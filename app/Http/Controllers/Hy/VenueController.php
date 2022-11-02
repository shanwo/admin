<?php
/**
 * User: Powerless
 * Date: 22/11/01
 * Blog: https://wzxaini9.cn
 */

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
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'keyword' => '搜索内容',
            'page'    => '页码',
            'size'    => '条数',
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
            'id' => 'required|integer|gt:0|exists:hy_venue,id',
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
            'name'      => 'required|string|min:2|max:32',
            'price'     => 'required|integer|between:0,65535',
            'image'     => 'required|url|max:128',
            'area_id'   => 'required|integer|gt:0|exists:cn_area,id',
            'address'   => 'required|integer|string|min:2|max:64',
            'sub_mchid' => 'required|integer|gt:0',
        ], [
            'name'      => '名称',
            'price'     => '单价',
            'image'     => '图片',
            'area_id'   => '地区',
            'address'   => '详细地址',
            'sub_mchid' => '商户号',
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
            'id'        => 'required|integer|gt:0|exists:hy_venue,id',
            'name'      => 'required|string|min:2|max:32',
            'price'     => 'required|integer|between:0,65535',
            'image'     => 'required|url|max:128',
            'area_id'   => 'required|integer|gt:0|exists:cn_area,id',
            'address'   => 'required|integer|string|min:2|max:64',
            'sub_mchid' => 'required|integer|gt:0',
        ], [
            'id'        => 'ID',
            'name'      => '名称',
            'price'     => '单价',
            'image'     => '图片',
            'area_id'   => '地区',
            'address'   => '详细地址',
            'sub_mchid' => '商户号',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->venueService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:hy_venue,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->venueService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function cache()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:hy_venue,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->venueService->cache($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
