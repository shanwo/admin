<?php
/**
 * User: Powerless
 * Date: 23/04/29
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers\Hy;

use App\Http\Controllers\Controller;
use App\Services\Hy\RevenueService;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    protected $exportService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->revenueService = new RevenueService();
    }

    public function lists()
    {
        $this->validate([
            'venue_id'   => 'nullable|integer|gt:0',
            'user_id'    => 'nullable|integer|gt:0',
            'start_time' => 'nullable|date',
            'end_time'   => 'nullable|date|after_or_equal:start_time',
            'page'       => 'filled|integer|gt:0',
            'size'       => 'filled|integer|between:10,100',
        ], [
            'venue_id'   => '场地ID',
            'user_id'    => '用户ID',
            'start_time' => '开始时间',
            'end_time'   => '结束时间',
            'page'       => '页码',
            'size'       => '条数',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data = $this->revenueService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }
}