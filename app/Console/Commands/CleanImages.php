<?php
/**
 * User: Powerless
 * Date: 23/03/06
 * Blog: https://wzxaini9.cn
 */

namespace App\Console\Commands;

use App\Models\Hy\UploadModel;
use App\Models\Hy\VenueModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Qcloud\Cos\Client;

class CleanImages extends Command
{
    const ENABLE   = 1;
    const DELETE   = 0;
    const DURATION = 90;
    const ONEDAY   = 86400;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clean_images';
    protected $venueModel;
    protected $uploadModel;
    protected $cos;
    protected $config;

    /**
     * ShortLists constructor.
     * php artisan command:clean_images
     */
    public function __construct()
    {
        parent::__construct();
        $this->venueModel = new VenueModel();
        $this->uploadModel = new UploadModel();
        $name = 'cos';
        if (strcmp(app('env'), 'prod')) {
            $name .= '-test';
        }
        $config = Redis::GET('config:' . $name);
        if ($config) {
            $this->config = json_decode($config, true);
            $this->cos = new Client([
                'region'      => 'ap-beijing',
                'credentials' => [
                    'secretId'  => $this->config['app_id'],
                    'secretKey' => $this->config['secret'],
                ]
            ]);
        }
    }

    public function handle()
    {
        $venues = $this->venueModel->select('id', 'name')->get()->toArray();
        foreach ($venues as $k => $v) {
            echo "\r\n【开始清理图片】" . $v['name'];
            $this->clean($v['id']);
            echo "\r\n【结束清理图片】" . $v['name'];
        }
    }

    private function clean($vid)
    {
        $timeAt = date('Y-m-d H:i:s', strtotime("-3 month"));
        $where = [
            ['venue_id', '=', $vid],
            ['status', '<>', self::DELETE],
            ['created_at', '>', $timeAt]
        ];
        $info = $this->uploadModel->where($where)->orderby('created_at', 'DESC')->first(['id', 'created_at']);
        if (!$info) {
            $where[2] = ['created_at', '<', $timeAt];
            $info = $this->uploadModel->where($where)->orderby('created_at', 'DESC')->first(['id', 'created_at']);
            if (!$info) {
                echo "\r\n雪场ID：{$vid}无数据，日期约束： $timeAt";
                return false;
            }
            $timeAt = date('Y-m-d H:i:s', strtotime($info['created_at'] . " -7 day"));
        }
        $where[2] = ['created_at', '<', $timeAt];
        $lists = $this->uploadModel->select('id', 'venue_id', 'original_url', 'thumbnail_url', 'created_at')->where($where)->get()->toArray();
        try {
            echo "\r\n需要清理数量：" . count($lists);
            foreach (array_chunk($lists, 32) as $chunk) {
                $ids = $images = [];
                foreach ($chunk as $v) {
                    $ids[] = $v['id'];
                    $images[] = ['Key' => substr(parse_url($v['original_url'], PHP_URL_PATH), 1)];
                    $images[] = ['Key' => substr(parse_url($v['thumbnail_url'], PHP_URL_PATH), 1)];
                }
                echo "\r\n本批清理图片：" . implode(',', $ids);
                $this->cos->DeleteObjects(['Bucket' => $this->config['original_bucket'], 'Objects' => $images]);
                $this->uploadModel->whereIn('id', $ids)->update(['status' => self::DELETE]);
            }
            if (!Redis::EXISTS('del_images')) $this->delData($vid);
        } catch (\Exception $exception) {
            echo "\r\n雪场ID：{$vid}图片清理异常：" . $exception->getMessage();
            return false;
        }
    }

    private function delData($venueId)
    {
        $del = Redis::LRANGE('venue_days:' . $venueId, self::DURATION, -1);
        foreach ($del as $day) {
            $rKey = $venueId . ':' . $day;
            $hour = Redis::LRANGE('venue_hours:' . $rKey, 0, -1);
            foreach ($hour as $h) {
                Redis::DEL('venue_images:' . $rKey . ':' . $h);
            }
            Redis::DEL('venue_hours:' . $rKey);
        }
        Redis::LTRIM('venue_days:' . $venueId, 0, self::DURATION);
        Redis::SETEX('del_images', self::ONEDAY / 6, date('Y-m-d H:i:s'));
    }
}
