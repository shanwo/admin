<?php
/**
 * User: Powerless
 * Date: 2021/6/20
 * Blog: https://wzxaini9.cn
 * Tips: 用户模块
 */

namespace App\Http\Controllers;

use App\Services\AttentionService;
use App\Services\BrowseService;
use App\Services\UserService;
use App\Services\UserTagService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;
    protected $browseService;
    protected $userTagService;
    protected $attentionService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->userService = new UserService();
        $this->browseService = new BrowseService();
        $this->userTagService = new UserTagService();
        $this->attentionService = new AttentionService();
    }

    public function lists()
    {
        $this->validate([
            'area'    => 'nullable|integer|gt:0',
            'keyword' => 'nullable|string|max:32',
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'keyword' => '搜索内容',
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
            'id' => 'required|integer|gt:0',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $data = $this->userService->info($this->request);
        $data['tags'] = $this->userTagService->user($id);
        unset($data['api_token']);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'   => 'required|integer|gt:0',
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

    public function attention()
    {
        $this->validate([
            'id'   => 'required|integer|gt:0',
            'page' => 'filled|integer|gt:0',
            'size' => 'filled|integer|between:10,100',
        ], [
            'id'   => 'ID',
            'page' => '页码',
            'size' => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $this->userService->attention($id);
        $data = $this->attentionService->user($id, $this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function browse()
    {
        $this->validate([
            'id'   => 'required|integer|gt:0',
            'page' => 'filled|integer|gt:0',
            'size' => 'filled|integer|between:10,100',
        ], [
            'id'   => 'ID',
            'page' => '页码',
            'size' => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $this->userService->browses($id);
        $data = $this->browseService->user($id, $this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
