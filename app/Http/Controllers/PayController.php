<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\PayService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class PayController extends Controller
{
    protected $payService;
    protected $payment;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->payService = new PayService();
        $config = $this->service->configInfo('payment');
        $config['cert_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'apiclient_cert.pem');
        $config['key_path'] = app_path('Exceptions' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'apiclient_key.pem');
        $this->payment = Factory::Payment($config);
    }

    public function lists()
    {
        $this->validate([
            'channel' => 'filled|integer|gt:0',
        ], [
            'channel' => '渠道',
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

    }

    public function add()
    {

    }

    public function edit()
    {

    }

    public function del()
    {

    }
}
