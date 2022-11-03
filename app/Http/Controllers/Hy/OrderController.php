<?php
/**
 * User: Powerless
 * Date: 22/11/02
 * Blog: https://wzxaini9.cn
 */


namespace App\Http\Controllers\Hy;

use App\Http\Controllers\Controller;
use App\Services\Hy\OrderService;
use EasyWeChat\Pay\Application;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $payment;
    protected $orderService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $config = $this->service->configInfo('partner');
        $config['private_key'] = env('PRIVATE_KEY');
        $config['certificate'] = env('CERTIFICATE');
        $this->payment = new Application($config);
        $this->orderService = new OrderService();
    }

    public function lists()
    {
        $this->validate([
            'venue_id' => 'nullable|integer|gt:0',
            'order_sn' => 'nullable|alpha_num|between:8,16',
            'third_sn' => 'nullable|numeric|between:16,32',
            'user_id'  => 'nullable|integer|gt:0',
            'page'     => 'filled|integer|gt:0',
            'size'     => 'filled|integer|between:10,100',
        ], [
            'venue_id' => '场地ID',
            'order_sn' => '订单号',
            'third_sn' => '三方单号',
            'user_id'  => '用户ID',
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
        $info = $this->orderService->info(['order_sn' => $order_sn]);
        $venue = $this->service->redis::HGET('venues', $info['venue_id']);
        $venue = json_decode($venue, true);
        $order = $this->payment->getClient()->get('v3/pay/partner/transactions/out-trade-no/' . $order_sn, [
            'query' => [
                'sp_mchid'  => $this->payment->getConfig()->get('mch_id'),
                'sub_mchid' => $venue['sub_mchid']
            ]
        ])->toArray();
        $refund = $this->request->input('payer_total');
        if ($refund > $order['amount']['payer_total']) {
            $this->msg = '退款金额不可大于支付金额';
            return $this->returnApi();
        }
        $data = $this->payment->getClient()->postJson('v3/refund/domestic/refunds', [
            'sub_mchid'      => $order['sub_mchid'],
            'transaction_id' => $order['transaction_id'],     //商户订单号
            'out_refund_no'  => $order['out_trade_no'],       //商户退款单号
            'reason'         => '订单支付超时',                //退款原因
            'notify_url'     => env('APP_URL') . 'hy/pay/refund',
            'amount'         => [
                'refund'   => $refund,                              //退款金额
                'total'    => $order['amount']['total'],            //原订单金额
                'currency' => 'CNY'
            ]
        ])->toArray();
        if (!strcmp($data['status'] ?? '', 'SUCCESS')) {
            $this->code = 200;
            $this->msg = $data['user_received_account'] ?? '';
        } else {
            $this->msg = $data['status'];
        }
        return $this->returnApi($data);
    }
}
