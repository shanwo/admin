<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 * Tips: 用户模块
 */

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
    }

    public function lists()
    {
        $this->validate([
            'area'    => 'nullable|integer|gt:0',
            'type'    => 'nullable|integer|between:0,3',
            'keyword' => 'nullable|string|max:16',
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'type'    => '用户类型',
            'keyword' => '关键字',
            'page'    => '页码',
            'size'    => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->userService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_user,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->userService->info($this->request);
        unset($data['api_token']);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'   => 'required|integer|gt:0|exists:sw_user,id',
            'type' => 'required|integer|in:3',
        ], [
            'id'   => 'ID',
            'type' => '类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->userService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
