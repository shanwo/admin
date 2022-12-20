<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\ConfigModel;
use Illuminate\Support\Facades\Redis;

class Service
{
    const ENABLE          = 1;
    const DELETE          = 0;
    const DISABLE         = -1;
    const SORT_ALL        = 0;
    const SORT_DECORATION = 1;
    const SORT_TUTORIALS  = 2;
    const SORT_TEMPLATE   = 3;
    const SORT_lINT       = 4;
    const DURATION        = 90;
    const ONEDAY          = 86400;
    public $redis;
    public $page = 1;
    public $size = 5;
    protected $configModel;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->configModel = new ConfigModel();
    }

    public function configInfo($name)
    {
        $data = [];
        if (strcmp(app('env'), 'prod')) {
            $name .='-test';
        }
        $config = $this->redis::GET('config:' . $name);
        if ($config) {
            $data = json_decode($config, true);
        } else {
            $lists = $this->configModel->where(['autoload' => 1, 'status' => Service::ENABLE])->get()->toArray();
            foreach ($lists as $k => $v) {
                $this->redis::SET('config:' . $v['option_name'], $v['option_value']);
                if ($v['option_name'] == $name) {
                    $data = json_decode($v['option_value'], true);
                }
            }
        }
        return $data;
    }

    public function score($created)
    {
        $md = substr($created, 5);
        [$day, $time] = explode(' ', $md);
        [$hour, $minute, $second] = explode(':', $time);
        $score = bcadd(bcmul($minute, 60), $second);
        return [$day, $hour, $score];
    }
}
