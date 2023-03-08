<?php
/**
 * User: Powerless
 * Date: 23/03/08
 * Blog: https://wzxaini9.cn
 */

namespace App\Console\Commands;

use App\Models\Sw\UserAttributeModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class VipTimeCheck extends Command
{
    const PAGE_MIN = 1;
    const PAGE_MAX = 4;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:vip_time_check';

    protected $userAttributeModel;

    /**
     * ShortLists constructor.
     * php artisan command:vip_time_check
     */
    public function __construct()
    {
        parent::__construct();
        $this->userAttributeModel = new UserAttributeModel();
    }

    public function handle()
    {
        $where = [
            ['type', '=', 2],
            ['expired_at', '<=', date('Y-m-d H:00:00')]
        ];
        $users = $this->userAttributeModel->select('id', 'user_id', 'vip_type', 'pages')->where($where)->get()->toArray();
        foreach ($users as $v) {
            $update = [
                'type'       => 1,
                'vip_type'   => 0,
                'pages'      => bcsub($v['pages'], (self::PAGE_MAX - $v['vip_type'])),
                'expired_at' => null
            ];
            $this->userAttributeModel->whereKey($v['id'])->update($update);
            Redis::DEL('user_attribute:' . $v['user_id']);
        }
    }
}