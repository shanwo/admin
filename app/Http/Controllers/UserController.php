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
            'id'             => 'required|integer|gt:0|exists:sw_user,id',
            'browse_page'    => 'filled|integer|gt:0',
            'attention_page' => 'filled|integer|gt:0',
            'size'           => 'filled|integer|between:10,100',
        ], [
            'id'             => 'ID',
            'browse_page'    => '浏览页码',
            'attention_page' => '关注页码',
            'size'           => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $id = $this->request->input('id');
        $data = $this->userService->info($this->request);
        $data['attention'] = $this->attentionService->user($id, $this->request);
        $data['browse'] = $this->browseService->user($id, $this->request);
        $data['tags'] = $this->userTagService->user($id);
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
