<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 * Tips: 模板模块
 */

namespace App\Http\Controllers;

use App\Services\TemplateService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    protected $templateService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->templateService = new TemplateService();
    }

    public function lists()
    {
        $this->validate([
            'sort_id' => 'nullable|integer|gt:0|exists:sw_sort,id',
            'page'    => 'filled|integer|gt:0',
            'size'    => 'filled|integer|between:10,100',
        ], [
            'sort_id' => '分类ID',
            'page'    => '页码',
            'size'    => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->templateService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_template,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->templateService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'sort_id'               => 'required|integer|gt:0|exists:sw_sort,id',
            'name'                  => 'required|string|min:2|max:32',
            'order'                 => 'nullable|integer|between:0,255',
            'background'            => 'required|url|max:128',
            'type'                  => 'required|integer|between:1,3',
        ], [
            'sort_id'               => '分类ID',
            'name'                  => '名称',
            'order'                 => '排序',
            'background'            => '背景图',
            'type'                  => '类型',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->templateService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'                    => 'required|integer|gt:0|exists:sw_template,id',
            'sort_id'               => 'required|integer|gt:0|exists:sw_sort,id',
            'name'                  => 'required|string|min:2|max:32',
            'order'                 => 'nullable|integer|between:0,255',
            'preview'               => 'required|url|max:128',
            'background'            => 'required|url|max:128',
            'type'                  => 'required|integer|between:1,2',
            'head_schema'           => 'required|array|min:1|max:64',
            'head_data'             => 'required|array|min:1|max:64',
            'head_data.avatar'      => 'required|url|max:128',
            'head_data.nickname'    => 'required|string|between:2,16',
            'head_data.signature'   => 'nullable|string|max:128',
            'head_data.badge'       => 'nullable|url|max:128',
            'head_data.pendant'     => 'nullable|url|max:128',
            'center_schema'         => 'required|array|min:1|max:64',
            'center_data'           => 'required|array|max:64',
            'center_data.*.id'      => 'required|integer|gt:0|exists:sw_link,id',
            'center_data.*.url'     => 'required|url|max:128',
            'center_data.*.image'   => 'required|url|max:128',
            'center_data.*.content' => 'required|string|max:32',
            'bottom_schema'         => 'nullable|array|min:1|max:64',
            'bottom_data'           => 'nullable|array|max:64',
            'bottom_data.*.id'      => 'required|integer|gt:0|exists:sw_link,id',
            'bottom_data.*.type'    => 'required_with:bottom_data|alpha_num|in:str,url',
            'bottom_data.*.image'   => 'required_with:bottom_data|url|max:128',
            'bottom_data.*.content' => 'required_with:bottom_data|string|max:128',
        ], [
            'id'                    => 'ID',
            'sort_id'               => '分类ID',
            'name'                  => '名称',
            'order'                 => '排序',
            'preview'               => '预览图',
            'background'            => '背景图',
            'type'                  => '类型',
            'head_schema'           => '顶部规则',
            'head_data'             => '顶部数据',
            'head_data.avatar'      => '头像',
            'head_data.nickname'    => '昵称',
            'head_data.signature'   => '签名',
            'head_data.badge'       => '徽章',
            'head_data.pendant'     => '挂件',
            'center_schema'         => '中部规则',
            'center_data'           => '中部数据',
            'center_data.*.id'      => '按钮ID',
            'center_data.*.url'     => '按钮地址',
            'center_data.*.image'   => '按钮图像',
            'center_data.*.content' => '按钮内容',
            'bottom_schema'         => '底部规则',
            'bottom_data'           => '底部数据',
            'bottom_data.*.id'      => '图标ID',
            'bottom_data.*.type'    => '图标类型',
            'bottom_data.*.image'   => '图标图片',
            'bottom_data.*.content' => '图标内容',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->templateService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function status()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_template,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->templateService->status($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_template,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->templateService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
