<?php
/**
 * User: Powerless
 * Date: 2021/9/14
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->orderService = new OrderService();
    }

    public function lists()
    {
        $this->validate([
            'order_sn' => 'nullable|alpha_num|between:8,16',
            'third_sn' => 'nullable|numeric|between:16,32',
            'nickname' => 'nullable|string|max:16',
            'page'     => 'filled|integer|gt:0',
            'size'     => 'filled|integer|between:10,100',
        ], [
            'order_sn' => '订单号',
            'third_sn' => '三方单号',
            'nickname' => '用户昵称',
            'page'     => '页码',
            'size'     => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->orderService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
