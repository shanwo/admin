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
        $where[] = ['status', '<>', Service::DELETE];
        $vid = $request->input('venue_id');
        if ($vid) $where[] = ['venue_id', '=', $vid];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['user_id', '=', $uid];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['name', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->uploadModel->select('id', 'venue_id', 'user_id', 'name', 'thumbnail_url', 'exif', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function clean()
    {
        $where[] = ['status', '<>', Service::DELETE];
        $where[] = ['updated_at', '<', date('Y-m-d H:i:s', strtotime('-3 month'))];
        $lists = $this->uploadModel->select('id', 'original_url', 'thumbnail_url')->where($where)->get()->toArray();
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
        } catch (\Exception $exception) {
            return ['msg' => $exception->getMessage()];
        }
    }

    public function report($request)
    {
        $vid = $request->input('venue_id');
        $uid = $request->user()->id;
        $images = $request->input('images');
        foreach ($images as $k => $v) {
            $original = '/' . $v;
            $thumbnail = '-thumbnail/' . $v;//快速缩略图  https://cloud.tencent.com/document/product/436/44893
            $exif = $this->cos->ImageExif(['Bucket' => 'examplebucket-1250000000','Key' =>$original]);
            $insert[] = [
                'venue_id'      => $vid,
                'user_id'       => $uid,
                'name'          => $v,
                'original_url'  => $original,
                'thumbnail_url' => $thumbnail,
                'exif'          => $exif['Data']
            ];
        }
        return $this->uploadModel->insert($insert);
    }
}
