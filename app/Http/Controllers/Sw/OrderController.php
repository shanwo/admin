<?php
/**
 * User: Powerless
 * Date: 2021/9/14
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Sw;

use App\Http\Controllers\Controller;
use App\Services\Sw\OrderService;
use EasyWeChat\Pay\Application;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $payment;
    protected $orderService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $config = $this->service->configInfo('payment');
        $config['private_key'] = env('PRIVATE_KEY');
        $config['certificate'] = env('CERTIFICATE');
        $this->payment = new Application($config);
        $this->orderService = new OrderService();
    }

    public function lists()
    {
        $this->validate([
            'order_sn' => 'nullable|alpha_num|between:8,16',
            'third_sn' => 'nullable|numeric|digits_between:16,32',
            'user_id'  => 'nullable|integer|gt:0',
            'status'   => 'nullable|integer|in:0,10,20,30,40,50',
            'page'     => 'filled|integer|gt:0',
            'size'     => 'filled|integer|between:10,100',
        ], [
            'order_sn' => '订单号',
            'third_sn' => '三方单号',
            'user_id'  => '用户ID',
            'status'   => '状态',
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
            'order_sn'    => 'required|alpha_num|size:16',
            'reason'      => 'required|string|max:16',
            'payer_total' => 'required|integer|gt:0',
        ], [
            'order_sn'    => '订单编号',
            'reason'      => '退款原因',
            'payer_total' => '退款金额',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $order_sn = $this->request->input('order_sn');
        $order = $this->payment->getClient()->get('v3/pay/transactions/out-trade-no/' . $order_sn, [
            'query' => ['mchid' => $this->payment->getConfig()->get('mch_id')]
        ])->toArray();
        $refund = $this->request->input('payer_total');
        if ($refund > $order['amount']['payer_total']) {
            $this->msg = '退款金额不可大于支付金额';
            return $this->returnApi();
        }
        $data = $this->payment->getClient()->postJson('v3/refund/domestic/refunds', [
            'transaction_id' => $order['transaction_id'],           //商户订单号
            'out_refund_no'  => $order['out_trade_no'],             //商户退款单号
            'reason'         => $this->request->input('reason'),    //退款原因
            'notify_url'     => 'https://api.shanwo.vip/sw/pay/refund',
            'amount'         => [
                'refund'   => $refund,                              //退款金额
                'total'    => $order['amount']['total'],            //原订单金额
                'currency' => 'CNY'
            ]
        ])->toArray();
        if (!strcmp($data['status'] ?? '', 'PROCESSING')) {
            $this->code = 200;
            $this->msg = $data['user_received_account'] ?? '';
        } else {
            $this->msg = $data['status'];
        }
        return $this->returnApi($data);
    }
}
