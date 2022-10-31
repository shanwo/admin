<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Sw;

use App\Http\Controllers\Controller;
use App\Services\Sw\PayService;
use Illuminate\Http\Request;

class PayController extends Controller
{
    protected $payService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->payService = new PayService();
    }

    public function lists()
    {
        $this->validate([
            'channel' => 'nullable|integer|gt:0|exists:sw_user,id',
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'channel' => '渠道',
            'page'    => '页码',
            'size'    => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->payService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_pay,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->payService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'name'           => 'required|string|max:16',
            'original_price' => 'required|numeric|gt:0.01',
            'activity_price' => 'required|numeric|gte:0.00',
            'description'    => 'nullable|string|max:64',
            'channel'        => 'nullable|integer|gt:0|exists:sw_user,id',
            'type'           => 'required|integer|between:1,5',
        ], [
            'name'           => '名称',
            'original_price' => '原价',
            'activity_price' => '活动价',
            'description'    => '描述',
            'channel'        => '渠道类型',
            'type'           => '购买类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->payService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'             => 'required|integer|gt:0|exists:sw_pay,id',
            'name'           => 'required|string|max:16',
            'original_price' => 'required|numeric|gt:0.01',
            'activity_price' => 'required|numeric|gte:0.00',
            'description'    => 'nullable|string|max:64',
            'channel'        => 'nullable|integer|gte:0|exists:sw_user,id',
            'type'           => 'required|integer|between:1,5',
        ], [
            'id'             => 'ID',
            'name'           => '名称',
            'original_price' => '原价',
            'activity_price' => '活动价',
            'description'    => '描述',
            'channel'        => '渠道类型',
            'type'           => '购买类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->payService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'     => 'required|integer|gt:0|exists:sw_pay,id',
            'status' => 'required|integer|in:-1,1',
        ], [
            'id'     => 'ID',
            'status' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->payService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
