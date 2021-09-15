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

    public function lists($request)
    {
        $where = [];
        $nickname = $request->input('nickname');
        if ($nickname) $where[] = ['u.nickname', 'like', "%$nickname%"];
        $status = $request->input('status');
        if ($status) $where[] = ['f.status', '=', $status];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->feedbackModel->from('sw_feedback as f')
            ->select('f.id', 'u.nickname', 'f.contacts', 'f.content', 'f.status', 'f.created_at')
            ->leftJoin('sw_user as u', 'f.user_id', '=', 'u.id')
            ->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        return $this->feedbackModel->whereKey($id)->first();
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'result' => $request->input('result'),
            'status' => 1,
        ];
        return $this->feedbackModel->whereKey($id)->update($update);
    }
}
