<?php

namespace App\Services\Official;

use App\Models\ReplyModel;
use App\Services\Service;

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
        $where[] = ['status', '<>', Service::DELETE];
        $type = $request->input('type');
        if ($type) $where[] = ['type', '=', $type];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['keyword', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->replyModel->select('id', 'type', 'keyword', 'number', 'status', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->replyModel->whereKey($id)->first();
        $info['comment'] = json_decode($info['comment'], true);
        return $info;
    }

    public function add($request)
    {
        $insert = [
            'type'    => $request->input('type'),
            'keyword' => $request->input('keyword'),
            'comment' => json_encode($request->input('comment'), JSON_UNESCAPED_UNICODE),
        ];
        return $this->replyModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'type'    => $request->input('type'),
            'keyword' => $request->input('keyword'),
            'comment' => json_encode($request->input('comment'), JSON_UNESCAPED_UNICODE),
        ];
        return $this->replyModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $info = $this->replyModel->whereKey($id)->first();
        $status = $request->input('status');
        if ($status == 1) {
            $this->redis::HSET('reply', $info['keyword'], $info['comment']);
        } else {
            $this->redis::HDEL('reply', $info['keyword']);
        }
        $number = $this->redis::ZSCORE('reply_number', $info['keyword']);
        if ($number) {
            $this->replyModel->whereKey($id)->increment('number', $number);
            $this->redis::ZREM('reply_number', $info['keyword']);
        }
        return $this->replyModel->whereKey($id)->update(['status' => $status]);
    }
}
