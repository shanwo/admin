<?php

namespace App\Http\Controllers\Official;

use App\Http\Controllers\Controller;
use App\Services\OfficialService;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    protected $officialService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->officialService = new OfficialService();
    }

    public function lists()
    {
        $this->validate([
            'type' => 'nullable|alpha_num|in:image,video,voice',
            'page' => 'filled|integer|gt:0',
            'size' => 'filled|integer|between:5,20',
        ], [
            'type' => '类型',
            'page' => '页码',
            'size' => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->officialService->lists($this->request);
        if (!empty($data['errcode'])) {
            $this->msg = '(' . $data['errcode'] . ')' . $data['errmsg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'media_id' => 'required|alpha_num|min:16|max:64',
        ], [
            'media_id' => '素材ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->officialService->info($this->request);
        if (!empty($data['errcode'])) {
            $this->msg = '(' . $data['errcode'] . ')' . $data['errmsg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }
}