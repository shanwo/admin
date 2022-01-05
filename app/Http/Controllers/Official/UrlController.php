<?php

namespace App\Http\Controllers\Official;

use App\Http\Controllers\Controller;
use App\Services\OfficialService;
use Illuminate\Http\Request;

class UrlController extends Controller
{
    protected $officialService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->officialService = new OfficialService();
    }

    public function shorten()
    {
        $this->validate([
            'long_url' => 'required|url|max:256',
        ], [
            'long_url' => '长连接',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->officialService->shorten($this->request);
        if (!empty($data['errcode'])) {
            $this->msg = '(' . $data['errcode'] . ')' . $data['errmsg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }
}