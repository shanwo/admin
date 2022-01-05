<?php

namespace App\Http\Controllers\Official;

use App\Http\Controllers\Controller;
use App\Services\OfficialService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected $officialService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->officialService = new OfficialService();
    }

    public function info()
    {
        $data = $this->officialService->menuInfo();
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function update()
    {
        $this->validate([
            'button'              => 'required|array|min:1|max:3',
            'button.*.sub_button' => 'nullable|array|min:0|max:5',
        ], [
            'button'              => '菜单',
            'button.*.sub_button' => '子菜单',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->officialService->menuUpdate($this->request);
        if (!empty($data['errcode'])) {
            $this->msg = '(' . $data['errcode'] . ')' . $data['errmsg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }
}