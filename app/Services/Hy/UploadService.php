<?php

namespace App\Services\Hy;

use App\Models\Hy\UploadModel;
use App\Services\Service;
use Intervention\Image\Facades\Image;
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
        $insert = $res = [];
        foreach ($images as $k => $v) {
            $url = explode('/', $v);
            $url[0] = 'thumbnail';
            $thumbnail = implode('/', $url);
            $exif = $this->cos->ImageExif(['Bucket' => $this->config['original_bucket'], 'Key' => $v]);
            $data = json_decode($exif['Data'], true);
            unset($data['MakerNote']);
            unset($data['UserComment']);
            $insert[] = [
                'venue_id'      => $vid,
                'user_id'       => $uid,
                'name'          => end($url),
                'original_url'  => '//' . $exif['Location'],
                'thumbnail_url' => '//' . parse_url('http://' . $exif['Location'], PHP_URL_HOST) . DIRECTORY_SEPARATOR . $thumbnail,
                'exif'          => json_encode($data),
                'updated_at'    => $exif['DateTime'],
                'created_at'    => $exif['DateTimeOriginal'],
            ];
            $local = storage_path() . DIRECTORY_SEPARATOR . end($url);
            Image::make('http://' . $exif['Location'])
                ->resize(480, null, function ($constraint) {
                    $constraint->aspectRatio();   // 按比例调整图片大小
                    $constraint->upsize();        // 这里如果宽度不足时，保持原来尺寸
                })->save($local);
            $res[] = $this->cos->upload($this->config['original_bucket'], $thumbnail, fopen($local, "rb"));
        }
        $this->uploadModel->insert($insert);
        return $res;
    }
}
