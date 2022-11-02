<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 * Tips: 页面模块
 */

namespace App\Http\Controllers\Sw;

use App\Http\Controllers\Controller;
use App\Services\Sw\AttentionService;
use App\Services\Sw\BrowseService;
use App\Services\Sw\PageService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected $pageService;
    protected $browseService;
    protected $attentionService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->pageService = new PageService();
        $this->browseService = new BrowseService();
        $this->attentionService = new AttentionService();
    }

    public function lists()
    {
        $this->validate([
            'user_id' => 'nullable|integer|gt:0',
            'open'    => 'nullable|integer|between:0,1',
            'keyword' => 'nullable|string|max:32',
            'order'   => 'nullable|string|in:follow,browse',
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'user_id' => '用户ID',
            'open'    => '广场',
            'type'    => '用户类型',
            'keyword' => '搜索内容',
            'order'   => '排序',
            'page'    => '页码',
            'size'    => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->lists($this->request);
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
        $data = $this->pageService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function sort()
    {
        $this->validate([
            'id'    => 'required|integer|gt:0',
            'sort'  => 'required|integer|between:-8388608,8388607',
            'cache' => 'nullable|integer|between:0,3',
        ], [
            'id'    => 'ID',
            'sort'  => '排序值',
            'cache' => '缓存',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->sort($this->request);
        if (!empty($data['msg'])) {
            $this->msg = $data['msg'];
            return $this->returnApi();
        }
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id'     => 'required|integer|gt:0',
            'status' => 'required|integer|in:-1,1',
        ], [
            'id'     => 'ID',
            'status' => '状态',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->pageService->status($this->request);
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
        $this->pageService->updateAttention($id);
        $data = $this->attentionService->page($id, $this->request);
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
        $this->pageService->updateBrowse($id);
        $data = $this->browseService->page($id, $this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
