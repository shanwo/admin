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
            $tmp = [];
            $v = urldecode($v);
            $urlPath = parse_url('//' . $v, PHP_URL_PATH);
            $urlArr = explode('/', $urlPath);
            $name = end($urlArr);
            try {
                $exif = $this->cos->ImageExif(['Bucket' => $this->config['original_bucket'], 'Key' => $urlPath]);
                $data = json_decode($exif['Data'], true);
                if (!empty($data['code'])) $data = [];
                unset($data['MakerNote']);
                unset($data['UserComment']);
                unset($data['ExifVersion']);
                unset($data['FlashPixVersion']);
                unset($data['PrintImageMatching']);
                unset($data['InteroperabilityVersion']);
                unset($data['DateTime']);
                unset($data['DateTimeOriginal']);
                $local = storage_path() . DIRECTORY_SEPARATOR . $name;
                $down = $this->cos->getObject([ 'Bucket' => $this->config['original_bucket'], 'Key' => $urlPath, 'SaveAs' => $local]);
                var_dump($down);
                $blindWatermarkTemplate = new \Qcloud\Cos\ImageParamTemplate\BlindWatermarkTemplate();//创建盲水印参数模版实例
                $blindWatermarkTemplate->setType(3);//盲水印类型，有效值：1 半盲；2 全盲；3 文字
                $blindWatermarkTemplate->setImage($local);//设置盲水印图片地址
                $blindWatermarkTemplate->setText("Test");//设置盲水印文字
                $blindWatermarkTemplate->setLevel(3);//只对全盲水印（type=2）有效。level 的取值范围为{1,2,3}，默认值为1，level 值越大则图片受影响程度越大、盲水印效果越好。
                $picOperationsTemplate = new \Qcloud\Cos\ImageParamTemplate\PicOperationsTransformation();//创建图片持久化处理参数模版实例
                $picOperationsTemplate->setIsPicInfo(1);//设置是否返回原图信息，0不返回原图信息，1返回原图信息，默认为0
                $picOperationsTemplate->addRule($blindWatermarkTemplate, "resultobject");//设置图片持久化处理参数
                $result = $this->cos->putObject(array(
                    'Bucket' => $this->config['original_bucket'], 'Key' => $urlPath,
                    'Body' => fopen($local, 'rb'),
                    'PicOperations' => $picOperationsTemplate->queryString(),//生成图片持久化处理参数
                ));
                var_dump($result);die;
                $thumbnail = str_replace('original', 'thumbnail', $v);
//                Image::make('http://' . $exif['Location'])
//                    ->resize(480, null, function ($constraint) {
//                        $constraint->aspectRatio();   // 按比例调整图片大小
//                        $constraint->upsize();        // 这里如果宽度不足时，保持原来尺寸
//                    })->save($local);
                $upload = $this->cos->upload($this->config['original_bucket'], parse_url('//' . $thumbnail, PHP_URL_PATH), fopen($local, "rb"));
                $created_at = $data['DateTimeOriginal']['val'] ?? date('Y-m-d H:i:s');
                $insert[] = [
                    'venue_id'      => $vid,
                    'user_id'       => $uid,
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
            $tmp['name'] = $name;
            $res[] = $tmp;
        }
        if ($insert) $this->uploadModel->insert($insert);
        return ['images' => $res];
    }
}
