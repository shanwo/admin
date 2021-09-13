<?php
/**
 * User: Powerless
 * Date: 2021/7/11
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\FeedbackModel;

class FeedbackService extends Service
{
    protected $feedbackModel;

    public function __construct()
    {
        parent::__construct();
        $this->feedbackModel = new FeedbackModel();
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
