<?php
/**
 * User: Powerless
 * Date: 2021/7/11
 * Blog: https://wzxaini9.cn
 * Tips: 反馈模块
 */

namespace App\Http\Controllers;

use App\Services\FeedbackService;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    protected $feedbackService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->feedbackService = new FeedbackService();
    }

    public function lists()
    {
        $this->validate([
            'page'     => 'filled|integer|gt:0',
            'size'     => 'filled|integer|between:10,100',
        ], [
            'page'     => '页码',
            'size'     => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->feedbackService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function info()
    {
        $this->validate([
            'id' => 'required|integer|gt:0|exists:sw_feedback,id',
        ], [
            'id' => 'ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->feedbackService->info($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function edit()
    {
        $this->validate([
            'id'     => 'required|integer|gt:0|exists:sw_feedback,id',
            'result' => 'required|string|max:512',
        ], [
            'id'     => 'ID',
            'result' => '处理结果',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->feedbackService->edit($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}
