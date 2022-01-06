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
    public $oneDay = 86400;
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
            $lists = $this->configModel->where(['autoload' => 1, 'is_delete' => Service::DELETE_NO])->get()->toArray();
            foreach ($lists as $k => $v) {
                $this->redis::SET('config:' . $v['option_name'], $v['option_value']);
                if ($v['option_name'] == $name) {
                    $data = json_decode($v['option_value'], true);
                }
            }
        }
        return $data;
    }
}
