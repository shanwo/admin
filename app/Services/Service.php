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
    const DELETE_YES = -1;
    const DELETE_NO = 1;
    const SORT_ALL = 0;
    const SORT_DECORATION = 1;
    const SORT_TUTORIALS = 2;
    const SORT_TEMPLATE = 3;
    const SORT_lINT = 4;
    public $redis;
    public $page = 1;
    public $size = 5;
    public $timeOut = 86400 * 30;
    protected $configModel;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->configModel = new ConfigModel();
    }

    public function configInfo($name)
    {
        $data = [];
        $config = $this->redis::GET('config:' . $name);
        if ($config) {
            $data = json_decode($config, true);
        } else {
            $lists = $this->configModel->where(['autoload' => 1])->get()->toArray();
            foreach ($lists as $k => $v) {
                $v['option_value'] = json_decode($v['option_value'], true);
                $this->redis::SET('config:' . $v['option_name'], json_encode($v, JSON_UNESCAPED_UNICODE));
                if ($v['option_name'] == $name) {
                    $data = $v;
                }
            }
        }
        return $data['option_value'];
    }
}
