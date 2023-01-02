<?php
/**
 * User: Powerless
 * Date: 2021/6/3
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\Service;
use GuzzleHttp\Client;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class Controller extends BaseController
{
    use ValidatesRequests;

    const TASK_TYPE = 1;        //订单导出
    protected Client $client;
    protected object $request;
    protected Service $service;
    protected int $code = 100;
    protected string $msg = '';
    protected array $levels = [
        100 => 'debug',
        200 => 'info',
        300 => 'notice',
        400 => 'notice',
    ];
    protected array $menu = [
        'hy/upload/lists',
        'hy/upload/clean',
        'hy/upload/del',
        'hy/upload/report',
        'hy/venue/lists',
        'upload/secret'
    ];

    public function __construct($request)
    {
        $this->request = $request;
        $this->client = new Client();
        $this->service = new Service();
        $this->role();
    }

    public function role()
    {
        if (in_array($this->request->path(), $this->menu)) {
            $user = $this->request->user();
            if (!$user) return $this->returnApi();
            $attribute = $this->service->redis::GET('user_attribute:' . $user->id);
            $attribute = json_decode($attribute, true);
            $admin = $this->service->redis::SISMEMBER('roles', $user->id);
            if (($attribute['type'] ?? 0) != 2 && !$admin) {
                $this->msg = '非管理员，无权访问';
                $this->code = 400;
                return $this->returnApi();
            }
        }
    }

    /**
     * 验证器
     * @param array $rules
     * @param array $customAttributes
     * @param array $messages
     */
    public function validate(array $rules, array $customAttributes = [], array $messages = [])
    {
        $this->code = 400;
        $validator = $this->getValidationFactory()->make($this->request->all(), $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $this->msg = current($errors);
        }
    }

    /**
     * 需要返回的数据
     * @param object $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function returnApi($data = [])
    {
        $result = [
            'request' => $this->request->all(),
            'data'    => (object)$data,
            'code'    => (int)$this->code,
            'msg'     => $this->msg,
            'uuid'    => Uuid::uuid6(),
        ];
        Log::log($this->levels[intval($this->code / 100) * 100], 'Return Api', $result);
        \Illuminate\Support\Facades\DB::disconnect('mysql');
        $this->service->redis::SELECT(3);
        $key = app('env') .':adminReturnLog:' . date('Y-m:d:') . $result['uuid'];
        $this->service->redis::SETEX($key, $this->service::ONEDAY * 7, json_encode($result, JSON_UNESCAPED_UNICODE));
        return response()->json($result);
    }

    /**
     * 功能：对字符串进行加密处理
     * @param string $str
     * 需要加密的内容.
     * @param string $key
     * 加密字符串
     * @return string
     * 加密后的内容
     */
    public function passportEncrypt(string $str, string $key): string
    {
        $encrypt_key = md5(rand(0, 32000));
        $ctr = 0;
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr] . ($str[$i] ^ $encrypt_key[$ctr++]);
        }
        return base64_encode($this->passportKey($tmp, $key));
    }

    /**
     * 辅助函数
     * @param $str
     * @param $encrypt_key
     * @return string
     */
    private function passportKey($str, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $str[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }

    /**
     * 功能：对字符串进行解密处理
     * @param string $str
     * 需要解密的密文.
     * @param string $key
     * 加密字符串
     * @return string
     * 解出的内容
     */
    public function passportDecrypt(string $str, string $key): string
    {
        $str = $this->passportKey(base64_decode($str), $key);
        $tmp = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $md5 = $str[$i];
            $tmp .= $str[++$i] ^ $md5;
        }
        return $tmp;
    }
}
