<?php
/**
 * User: Powerless
 * Date: 2021/9/14
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\OrderService;
use EasyWeChat\Factory;
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

    public function refund()
    {
        $this->validate([
            'order_sn' => 'required|alpha_num|size:16',
        ], [
            'order_sn' => '订单编号',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $order_sn = $this->request->input('order_sn');
        $config = $this->service->configInfo('payment');
        $config['cert_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'apiclient_cert.pem');
        $config['key_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'apiclient_key.pem');
        $payment = Factory::Payment($config);
        $order = $payment->order->queryByOutTradeNumber($order_sn);
        $data = $payment->refund->byTransactionId($order['transaction_id'], $order['out_trade_no'], $order['total_fee'], $order['cash_fee'], [
            'refund_desc' => '测试退款',
        ]);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
