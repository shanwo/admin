<?php

namespace App\Services\Hy;

use App\Models\Hy\UploadModel;
use App\Services\Service;
use Qcloud\Cos\Client;

class UploadService extends Service
{
    protected $uploadModel;
    protected $cos;
    protected $config;

    public function __construct()
    {
        parent::__construct();
        $this->uploadModel = new UploadModel();
        $this->config = $this->configInfo('cos');
        $this->cos = new Client([
            'region'      => 'ap-beijing',
            'credentials' => [
                'secretId'  => $this->config['app_id'],
                'secretKey' => $this->config['secret'],
            ]
        ]);
    }

    public function lists($request)
    {
        $where[] = ['u.status', '<>', Service::DELETE];
        $vid = $request->input('venue_id');
        if ($vid) $where[] = ['u.venue_id', '=', $vid];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['u.user_id', '=', $uid];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['u.name', 'like', "%$keyword%"];
        $start_time = $request->input('start_time');
        if ($start_time) $where[] = ['u.created_at', '>=', $start_time . ' 00:00:00'];
        $end_time = $request->input('end_time');
        if ($end_time) $where[] = ['u.created_at', '<=', $end_time . ' 23:59:59'];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->uploadModel->from('hy_upload as u')
            ->select('u.id', 'venue.name as venue_name', 'u.user_id', 'u.name', 'u.thumbnail_url', 'u.exif', 'u.updated_at', 'u.created_at')
            ->leftjoin('hy_venue as venue', 'venue.id', '=', 'u.venue_id')
            ->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('u.id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function del($request)
    {
        $id = $request->input('id');
        $info = $this->uploadModel->whereKey($id)->first();
        [$day, $hour, $score] = $this->score($info['created_at']);
        $rKey = $info['venue_id'] . ':' . $day;
        $this->redis::ZREMRANGEBYSCORE('venue_images:' . $rKey . ':' . $hour, $score, $score);
        $zcard = $this->redis::ZCARD('venue_images:' . $rKey . ':' . $hour);
        if (!$zcard) {
            $this->redis::LREM('venue_hours:' . $rKey, 0, $hour);
            $len = $this->redis::LLEN('venue_hours:' . $rKey);
            if (!$len) $this->redis::LREM('venue_days:' . $info['venue_id'], 0, $day);
        }
        $this->cos->DeleteObjects([
            'Bucket'  => $this->config['original_bucket'],
            'Objects' => [['Key' => substr(parse_url($info['original_url'], PHP_URL_PATH), 1)],
                          ['Key' => substr(parse_url($info['thumbnail_url'], PHP_URL_PATH), 1)]]
        ]);
        return $this->uploadModel->whereKey($id)->delete();
    }

    public function download($request)
    {
        $id = $request->input('id');
        return $this->uploadModel->whereKey($id)->first('original_url');
    }

    public function clean($request)
    {
        $vid = $request->input('venue_id');
        $where[] = ['status', '<>', Service::DELETE];
        $where[] = ['user_id', '=', $request->user()->id];
        if ($vid) $where[] = ['venue_id', '=', $vid];
        $where[] = ['created_at', '<', date('Y-m-d H:i:s', strtotime('-3 month'))];
        $lists = $this->uploadModel->select('id', 'venue_id', 'original_url', 'thumbnail_url')->where($where)->get()->toArray();
        try {
            $ids = $original = $thumbnail = [];
            foreach (array_chunk($lists, 1) as $chunk) {
                foreach ($chunk as $v) {
                    $ids[] = $v['id'];
                    $original[] = ['Key' => substr(parse_url($v['original_url'], PHP_URL_PATH), 1)];
                    $thumbnail[] = ['Key' => substr(parse_url($v['thumbnail_url'], PHP_URL_PATH), 1)];
                }
                $this->cos->DeleteObjects(['Bucket' => $this->config['original_bucket'], 'Objects' => $original]);
                $this->cos->DeleteObjects(['Bucket' => $this->config['original_bucket'], 'Objects' => $thumbnail]);
                $this->uploadModel->whereIn('id', $ids)->update(['status' => Service::DELETE]);
            }
            if (!$this->redis::EXISTS('del_images')) {
                $vids = array_filter(array_unique(array_column($lists, 'venue_id')));
                foreach ($vids as $value) {
                    $this->delData($value);
                }
            }
            return $ids;
        } catch (\Exception $exception) {
            return ['msg' => $exception->getMessage()];
        }
    }

    public function report($request)
    {
        $venueId = $request->input('venue_id');
        $venue = $this->redis::HGET('venues', $venueId);
        $venue = json_decode($venue, true);
        $image = $request->input('image');
        $image = urldecode($image);
        $urlPath = parse_url($image, PHP_URL_PATH);
        $urlArr = explode('/', $urlPath);
        $name = end($urlArr);
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $name) > 0) {
            $res['msg'] = '图片名称不可包含中文字符';
            $res['code'] = 401;
            return $res;
        }
        $res['path'] = $image;
        $res['name'] = $name;
        $distinct = $this->uploadModel->select('id', 'name')
            ->where([['venue_id', '=', $venueId], ['user_id', '=', $request->user()->id], ['name', '=', $name],
                     ['updated_at', '>=', date('Y-m-d')], ['status', '<>', Service::DELETE]])->first();
        if ($distinct) {
            $res['msg'] = '今日已上传过此图片，无需重复上传';
            return $res;
        }
        $view = new \Qcloud\Cos\ImageParamTemplate\ImageViewTemplate();         //创建快速缩略模板参数模版实例
        $watermark = new \Qcloud\Cos\ImageParamTemplate\TextWatermarkTemplate();//创建文字水印参数模版实例
        $view->setMode(2);
        $view->setWidth(750);
        $view->setQuality(1, 80);
        $watermark->setText($venue['name']);
        $watermark->setFont('simhei黑体.ttf');
        $watermark->setFontsize(16);
        $watermark->setDegree(-45);
        $watermark->setDx(8);
        $watermark->setDy(8);
        $local = (env('APP_STORAGE') ?? storage_path()) . DIRECTORY_SEPARATOR . $name;
        $thumbnail = parse_url(str_replace('original', 'thumbnail', $image), PHP_URL_PATH);
        try {
            $this->cos->getObject([
                'Bucket' => $this->config['original_bucket'], 'Key' => $urlPath,
                'SaveAs' => $local, 'ImageHandleParam' => $view->queryString(),
            ]);
            $this->cos->upload($this->config['original_bucket'], $thumbnail, fopen($local, "rb"));
            $this->cos->getObject([
                'Bucket' => $this->config['original_bucket'], 'Key' => $thumbnail,
                'SaveAs' => $local, 'ImageHandleParam' => $watermark->queryString(),
            ]);
            $upload = $this->cos->upload($this->config['original_bucket'], $thumbnail, fopen($local, "rb"));
            $exif = $this->cos->ImageExif(['Bucket' => $this->config['original_bucket'], 'Key' => $urlPath]);
            $data = json_decode($exif['Data'], true);
            if (!empty($data['code'])) $data = [];
            $datetime = $data['DateTimeOriginal']['val'] ?? $data['DateTime']['val'] ?? date('Y-m-d H:i:s');
            $created_at = date('Y-m-d H:i:s',strtotime($datetime));
            unset($data['MakerNote']);
            unset($data['UserComment']);
            unset($data['ExifVersion']);
            unset($data['FlashPixVersion']);
            unset($data['PrintImageMatching']);
            unset($data['InteroperabilityVersion']);
            unset($data['DateTime']);
            unset($data['DateTimeOriginal']);
            $insert = [
                'venue_id'      => $venueId,
                'user_id'       => $request->user()->id,
                'name'          => $name,
                'original_url'  => 'https://' . $exif['Location'],
                'thumbnail_url' => 'https://' . $upload['Location'],
                'exif'          => json_encode($data),
                'created_at'    => $created_at,
            ];
            $insert['id'] = $this->uploadModel->insertGetId($insert);
            $this->setData($insert);
        } catch (\Exception $exception) {
            $res['msg'] = $exception->getMessage();
        }
        return $res;
    }

    private function setData($data)
    {
        [$day, $hour, $score] = $this->score($data['created_at']);
        $rKey = $data['venue_id'] . ':' . $day;
        if (!$this->redis::EXISTS('venue_images:' . $rKey . ':' . $hour)) {
            if (!$this->redis::EXISTS('venue_hours:' . $rKey)) {
                $this->redis::LPUSH('venue_hours:' . $rKey, $hour);
                if (!$this->redis::EXISTS('venue_days:' . $data['venue_id'])) {
                    $this->redis::LPUSH('venue_days:' . $data['venue_id'], $day);
                } else {
                    $days = $this->redis::LRANGE('venue_days:' . $data['venue_id'], 0, -1);
                    if (!in_array($day, $days)) $this->redis::LPUSH('venue_days:' . $data['venue_id'], $day);
                }
            } else {
                $hours = $this->redis::LRANGE('venue_hours:' . $rKey, 0, -1);
                if (!in_array($hour, $hours)) $this->redis::LPUSH('venue_hours:' . $rKey, $hour);
            }
        }
        $img = [
            'id'   => $data['id'],
            'name' => $data['name'],
            'url'  => $data['thumbnail_url']
        ];
        $this->redis::ZADD('venue_images:' . $rKey . ':' . $hour, $score, json_encode($img));
    }

    private function delData($venueId)
    {
        $del = $this->redis::LRANGE('venue_days:' . $venueId, self::DURATION, -1);
        foreach ($del as $day) {
            $rKey = $venueId . ':' . $day;
            $hour = $this->redis::LRANGE('venue_hours:' . $rKey, 0, -1);
            foreach ($hour as $h) {
                $this->redis::DEL('venue_images:' . $rKey . ':' . $h);
            }
            $this->redis::DEL('venue_hours:' . $rKey);
        }
        $this->redis::LTRIM('venue_days:' . $venueId, 0, self::DURATION);
        $this->redis::SETEX('del_images', self::ONEDAY / 6, date('Y-m-d H:i:s'));
    }

    private function score($created)
    {
        $md = substr($created, 5);
        [$day, $time] = explode(' ', $md);
        [$hour, $minute, $second] = explode(':', $time);
        $score = bcadd(bcmul($minute, 60), $second);
        return [$day, $hour, $score];
    }

}
