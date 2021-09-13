<?php
/**
 * User: Powerless
 * Date: 21/09/13
 * Blog: https://wzxaini9.cn
 */

namespace App\Http\Controllers;

use App\Services\SortService;
use Illuminate\Http\Request;

class SortController extends Controller
{
    protected $sortService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->sortService = new SortService();
    }

    public function lists()
    {
        $this->validate([
            'type' => 'nullable|integer|gt:0|exists:sw_sort,id',
        ], [
            'type' => '分类ID',
        ]);
        if ($this->msg) {
            return $this->returnApi();
        }
        $data['lists'] = $this->sortService->lists($this->request);
        $this->code = 200;
        return $this->returnApi($data);
    }

    public function add()
    {

    }

    public function del()
    {

    }
}
