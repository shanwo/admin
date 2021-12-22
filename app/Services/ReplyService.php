<?php

namespace App\Services;

use App\Models\ReplyModel;

class ReplyService extends Service
{
    protected $replyModel;

    public function __construct()
    {
        parent::__construct();
        $this->replyModel = new ReplyModel();
    }

    public function lists($request)
    {
        $where = [];
        $type = $request->input('type');
        if ($type) $where[] = ['type', '=', $type];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['keyword', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->replyModel->select('id', 'type', 'keyword', 'number', 'status', 'created_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        return $this->replyModel->whereKey($id)->first();
    }

    public function add($request)
    {
        $keyword = $request->input('keyword');
        $comment = json_encode($request->input('comment'), JSON_UNESCAPED_UNICODE);
        $insert = [
            'type'    => $request->input('type'),
            'keyword' => $keyword,
            'comment' => $comment,
        ];
        $this->redis::HSET('reply', $keyword, $comment);
        return $this->replyModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $keyword = $request->input('keyword');
        $comment = json_encode($request->input('comment'), JSON_UNESCAPED_UNICODE);
        $update = [
            'type'    => $request->input('type'),
            'keyword' => $keyword,
            'comment' => $comment,
        ];
        $this->redis::HSET('reply', $keyword, $comment);
        return $this->replyModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $this->replyModel->whereKey($id)->update(['status' => Service::DELETE_YES]);
        $info = $this->replyModel->whereKey($id)->first();
        $this->redis::HDEL('reply_number', $info['keyword']);
        return $this->redis::HDEL('reply', $info['keyword']);
    }
}