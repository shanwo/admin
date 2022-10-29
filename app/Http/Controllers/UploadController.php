<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 * Tips: 上传模块
 */

namespace App\Http\Controllers;

use App\Services\UploadService;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    protected $uploadService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->uploadService = new UploadService();
    }
    public function secret()
    {
        $this->code = 200;
        $data = $this->uploadService->secret($this->request->user());
        return $this->returnApi($data);
    }

    public function lists()
    {
        $this->validate([
            'nickname' => 'nullable|string|max:16',
            'page'     => 'filled|integer|gt:0',
            'size'     => 'filled|integer|between:10,100',
        ], [
            'nickname' => '用户昵称',
            'page'     => '页码',
            'size'     => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->uploadService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function file()
    {
        $this->validate([
            'file' => 'required',
        ], [
            'file' => '文件'
        ]);
        $data = [];
        try {
            $data = $this->uploadService->update($this->request);
            $this->code = 200;
            $this->msg = '';
        } catch (\Exception $e) {
            $this->msg = $e->getMessage();
            $this->code = 400;
        }
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_link,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->uploadService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
