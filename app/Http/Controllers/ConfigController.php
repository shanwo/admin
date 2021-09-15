<?php
/**
 * User: Powerless
 * Date: 21/09/13
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\ConfigService;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    protected $configService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->configService = new ConfigService();
    }

    public function lists()
    {
        $this->validate([
            'page'        => 'filled|integer|gt:0',
            'size'        => 'filled|integer|between:10,100',
        ], [
            'page'        => '页码',
            'size'        => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->configService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_config,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->configService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {
        $this->validate([
            'option_name'  => 'required|string|min:2|max:32',
            'option_value' => 'required|array|max:1',
            'autoload'     => 'required|integer|between:0,1',
        ], [
            'option_name'  => '配置名称',
            'option_value' => '配置值',
            'autoload'     => '自动加载',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->configService->add($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'           => 'required|integer|gt:0|exists:sw_config,id',
            'option_name'  => 'required|string|min:2|max:32',
            'option_value' => 'required|array|max:1',
            'autoload'     => 'required|integer|between:0,1',
        ], [
            'id'           => 'ID',
            'option_name'  => '配置名称',
            'option_value' => '配置值',
            'autoload'     => '自动加载',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->configService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function del()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_config,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->configService->del($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
