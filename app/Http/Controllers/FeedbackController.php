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

    }

    public function info()
    {

    }

    public function status()
    {

    }
}
