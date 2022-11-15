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
        $this->cos->DeleteObject([
            'Bucket' => $this->config['original_bucket'],
            'Key'    => $info['url'],
        ]);
        return $this->uploadModel->whereKey($id)->delete();
    }

    public function clean($request)
    {
        $where[] = ['status', '<>', Service::DELETE];
        $where[] = ['user_id', '=', $request->user()->id];
        $where[] = ['created_at', '<', date('Y-m-d H:i:s', strtotime('-3 month'))];
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
            return $ids;
        } catch (\Exception $exception) {
            return ['msg' => $exception->getMessage()];
        }
    }

    public function report($request)
    {
        $vid = $request->input('venue_id');
        $venue = $this->redis::HGET('venues', $vid);
        $venue = json_decode($venue, true);
        $images = $request->input('images');
        $insert = $res = [];
        $view = new \Qcloud\Cos\ImageParamTemplate\ImageViewTemplate();         //创建快速缩略模板参数模版实例
        $watermark = new \Qcloud\Cos\ImageParamTemplate\TextWatermarkTemplate();//创建文字水印参数模版实例
        $view->setMode(2);
        $view->setWidth(600);
        $view->setQuality(1, 75);
        $watermark->setText($venue['name']);
        $watermark->setFont('simhei黑体.ttf');
        $watermark->setFontsize(16);
        $watermark->setDegree(-45);
        $watermark->setDx(8);
        $watermark->setDy(8);
        foreach ($images as $k => $v) {
            $tmp = [];
            $v = urldecode($v);
            $urlPath = parse_url('//' . $v, PHP_URL_PATH);
            $urlArr = explode('/', $urlPath);
            $name = end($urlArr);
            if (in_array($name, ['ypt1.jpg', 'ypt2.jpg', 'ypt3.jpg', 'ypt4.jpg', 'ypt5.jpg', 'ypt6.jpg', 'ypt.jpg'])) {
                $tmp['msg'] = 'image upload error';
                continue;
            }
            $local = (env('APP_STORAGE') ?? storage_path()) . DIRECTORY_SEPARATOR . $name;
            $thumbnail = parse_url('//' . str_replace('original', 'thumbnail', $v), PHP_URL_PATH);
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
//                Image::make('http://' . $exif['Location'])
//                    ->resize(480, null, function ($constraint) {
//                        $constraint->aspectRatio();   // 按比例调整图片大小
//                        $constraint->upsize();        // 这里如果宽度不足时，保持原来尺寸
//                    })->save($local);
                $exif = $this->cos->ImageExif(['Bucket' => $this->config['original_bucket'], 'Key' => $urlPath]);
                $data = json_decode($exif['Data'], true);
                if (!empty($data['code'])) $data = [];
                $created_at = $data['DateTimeOriginal']['val'] ?? $data['DateTime']['val'] ??date('Y-m-d H:i:s');
                unset($data['MakerNote']);
                unset($data['UserComment']);
                unset($data['ExifVersion']);
                unset($data['FlashPixVersion']);
                unset($data['PrintImageMatching']);
                unset($data['InteroperabilityVersion']);
                unset($data['DateTime']);
                unset($data['DateTimeOriginal']);
                $insert[] = [
                    'venue_id'      => $vid,
                    'user_id'       => $request->user()->id,
                    'name'          => $name,
                    'original_url'  => '//' . $exif['Location'],
                    'thumbnail_url' => '//' . $upload['Location'],
                    'exif'          => json_encode($data),
                    'created_at'    => $created_at,
                ];
                $tmp['msg'] = 'success';
            } catch (\Exception $exception) {
                $tmp['msg'] = $exception->getMessage();
            }
            $tmp['path'] = $v;
            $tmp['name'] = $name;
            $res[] = $tmp;
        }
        if ($insert) $this->uploadModel->insert($insert);
        return ['images' => $res];
    }
}
