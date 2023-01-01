<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UploadModel;
use Qcloud\Cos\Client;
use QCloud\COSSTS\Sts;

class UploadService extends Service
{
    protected $uploadModel;
    private $config;
    private $cos;

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

    /**
     * 前端上传签名
     * @return array
     * @throws \Exception
     */
    public function secret($user)
    {
        if ($this->redis::EXISTS('secret:' . $user->id)) {
            $secret = $this->redis::GET('secret:' . $user->id);
            $data = json_decode($secret, true);
        } else {
            $path = '/';
            if (strcmp(env('APP_ENV'), 'prod') != 0) {
                $path .= app('env') . '/';
            }
            $path .= 'original/user_' . $user->id . date('/Y-m/d/');
            $sts = new Sts();
            $config = [
                'url'             => 'https://sts.tencentcloudapi.com/',
                'domain'          => 'sts.tencentcloudapi.com',
                'proxy'           => '',
                'secretId'        => $this->config['app_id'], // 固定密钥,若为明文密钥，请直接以'xxx'形式填入，不要填写到getenv()函数中
                'secretKey'       => $this->config['secret'], // 固定密钥,若为明文密钥，请直接以'xxx'形式填入，不要填写到getenv()函数中
                'bucket'          => $this->config['original_bucket'], // 换成你的 bucket
                'region'          => 'ap-beijing', // 换成 bucket 所在园区
                'durationSeconds' => 7200, // 密钥有效期
                'allowPrefix'     => $path.'*',
                'allowActions'    => [
                    // 所有 action 请看文档 https://cloud.tencent.com/document/product/436/31923
                    // 简单上传
                    'name/cos:PutObject',               //上传一个对象至存储桶
                    'name/cos:PostObject',              //使用表单请求上传对象
                    // 分片上传
                    'name/cos:InitiateMultipartUpload', //初始化分块上传
                    'name/cos:ListMultipartUploads',    //查询正在进行中的分块上传信息
                    'name/cos:ListParts',               //查询特定分块上传操作中的已上传的块
                    'name/cos:UploadPart',              //分块上传文件
                    'name/cos:CompleteMultipartUpload'  //完成整个文件的分块上传
                ]
            ];
            $data = $sts->getTempKeys($config);
            $data['path'] = $path;
            $data['bucket'] = $config['bucket'];
            $data['region'] = $config['region'];
            $this->redis::SETEX('secret:' . $user->id, 1800, json_encode($data));
        }
        return $data;
    }

    /**
     * @param $ext
     * 返回后缀所对应的文件类型
     * @return int
     */
    private function getType($ext)
    {
        $image = ['swf'];
        $compression = ['rar', 'zip', 'apk', 'cab', 'arj', 'lzh', 'tar', 'gz', 'ace', 'uue', 'bz2．jar', 'iso', 'z', '7z ', 'xz', 'rar5', 'exe', 'lz', 'mdf', 'zipx'];
        $office = ['doc', 'docx', 'docm', 'dotx', 'dotm', 'xls', 'xlsx', 'xlsm', 'xltx', 'xltm', 'xlsb', 'xlam', 'ppt', 'pptx', 'pptm', 'ppsx', 'ppsm', 'potx', 'potm', 'ppam', 'pdf', 'tmp', 'wps', 'wks', 'wcm', 'wdb'];
        $text = ['h', 'go', 'md', 'mod', 'json', 'jsonml', 'lock', 'php', 'py', 'sh', 'sql', 'xml', 'xsl', 'bak', 'ldf'];
        switch ($ext) {
            case in_array($ext, $image):
                $type = 1;
                break;
            case in_array($ext, $compression):
                $type = 2;
                break;
            case in_array($ext, $office):
                $type = 3;
                break;
            case in_array($ext, $text):
                $type = 4;
                break;
            default:
                $type = 0;
        }
        $fileTypes = ['application', 'image', 'compression', 'office', 'text'];
        return $fileTypes[$type];
    }

    /**
     * 上传文件
     * @param $request
     * @return mixed
     */
    public function update($request)
    {
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        [$type] = explode('/', $file->getMimeType());
        if ($type == 'application') $type = $this->getType($ext);
        if (!strcmp(app('env'), 'prod')) {
            $path = $type . date('/Y/m/');
        } else {
            $path = app('env') . '/' . $type . date('/Y/m/');
        }
        $name = dechex(LARAVEL_START * 10000);
        if ($ext) {
            $name = $name . '.' . $ext;
        }
        $this->cos->upload($this->config['target_bucket'], $path . $name, fopen($file->getRealPath(), "rb"));
        $data['name'] = $name;
        $data['url'] = env('APP_CDN') .'/'. $path . $name;
        $insert = [
            'user_id' => $request->user()->id,
            'url'     => $path . $name,
            'type'    => $type,
            'name'    => $name
        ];
        $this->uploadModel->insertGetId($insert);
        return $data;
    }

    public function lists($request)
    {
        $where = [];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['user_id', '=', $uid];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->uploadModel
            ->select('id', 'user_id', 'url', 'type', 'name', 'created_at')
            ->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function del($request)
    {
        $id = $request->input('id');
        $info = $this->uploadModel->whereKey($id)->first();
        $this->cos->DeleteObject([
            'Bucket' => $this->config['target_bucket'],
            'Key'    => $info['url'],
        ]);
        return $this->uploadModel->whereKey($id)->delete();
    }
}

