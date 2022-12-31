<?php
/**
 * User: Powerless
 * Date: 22/11/02
 * Blog: https://wzxaini9.cn
 */


namespace App\Http\Controllers\Hy;

use App\Http\Controllers\Controller;
use App\Services\Hy\OrderService;
use App\Services\Hy\ExportService;
use EasyWeChat\Pay\Application;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $payment;
    protected $taskService;
    protected $orderService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $config = $this->service->configInfo('partner');
        $config['private_key'] = env('PARTNER_PRIVATE_KEY');
        $config['certificate'] = env('PARTNER_CERTIFICATE');
        $this->payment = new Application($config);
        $this->taskService = new ExportService();
        $this->orderService = new OrderService();
    }

    public function lists()
    {
        $this->validate([
            'venue_id'   => 'required|integer|gt:0',
            'order_sn'   => 'nullable|alpha_num|between:8,16',
            'third_sn'   => 'nullable|numeric|digits_between:16,32',
            'user_id'    => 'nullable|integer|gt:0',
            'status'     => 'nullable|integer|in:0,10,20,30,40,50',
            'start_time' => 'nullable|date',
            'end_time'   => 'nullable|date|after_or_equal:start_time',
            'page'       => 'filled|integer|gt:0',
            'size'       => 'filled|integer|between:10,100',
        ], [
            'venue_id'   => '场地ID',
            'order_sn'   => '订单号',
            'third_sn'   => '三方单号',
            'user_id'    => '用户ID',
            'status'     => '状态',
            'start_time' => '开始时间',
            'end_time'   => '结束时间',
            'page'       => '页码',
            'size'       => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->orderService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function export()
    {
        $endTime = strtotime('+1 month', strtotime($this->request['start_time']));
        $this->validate([
            'venue_id'   => 'required|integer|gt:0',
            'order_sn'   => 'nullable|alpha_num|between:8,16',
            'third_sn'   => 'nullable|numeric|digits_between:16,32',
            'user_id'    => 'nullable|integer|gt:0',
            'status'     => 'nullable|integer|in:0,10,20,30,40,50',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after_or_equal:start_time|before_or_equal:' . date('Y-m-d', $endTime),
        ], [
            'venue_id'   => '场地ID',
            'order_sn'   => '订单号',
            'third_sn'   => '三方单号',
            'user_id'    => '用户ID',
            'status'     => '状态',
            'start_time' => '开始时间',
            'end_time'   => '结束时间',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $this->taskService->export($this->request,Controller::TASK_TYPE);
        $this->msg = '导出任务已创建，请前往';
        $this->code = 200;
        return $this->returnApi();
    }

    public function info()
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
        $data = $this->orderService->info(['order_sn' => $order_sn]);
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
        $data = [];
        $order_sn = $this->request->input('order_sn');
        $info = $this->orderService->info(['order_sn' => $order_sn]);
        $venue = $this->service->redis::HGET('venues', $info['venue_id']);
        $venue = json_decode($venue, true);
        try {
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
                'transaction_id' => $order['transaction_id'],           //商户订单号
                'out_refund_no'  => $order['out_trade_no'],             //商户退款单号
                'reason'         => $this->request->input('reason'),    //退款原因
                'notify_url'     => 'https://api.chillman.cc/hy/pay/refund',
                'amount'         => [
                    'refund'   => $refund,                              //退款金额
                    'total'    => $order['amount']['total'],            //原订单金额
                    'currency' => 'CNY'
                ]
            ])->toArray();
            if (!strcmp($data['status'] ?? '', 'PROCESSING') || !strcmp($data['status'] ?? '', 'SUCCESS')) {
                $this->code = 200;
                $this->msg = $data['user_received_account'] ?? '';
            } else {
                $this->msg = $data['status'];
            }
        } catch (\Exception $e) {
            $this->msg = $e->getMessage();
            $this->code = 400;
        }
        return $this->returnApi($data);
    }
}
