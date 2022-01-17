<?php

namespace App\Services;

use App\Models\PushModel;

class PushService extends Service
{
    protected $pushModel;

    public function __construct()
    {
        parent::__construct();
        $this->pushModel = new PushModel();
    }

    public function lists($request)
    {
        $where[] = ['is_delete', '=', Service::DELETE_NO];
        $type = $request->input('start_type');
        if ($type) $where[] = ['start_type', '=', $type];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['name', 'like', "%$keyword%"];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->pushModel->select('id', 'name', 'start_type', 'start_method', 'start_time', 'status', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->pushModel->whereKey($id)->first();
        $info['rule'] = json_decode($info['rule'], true);
        $info['config'] = json_decode($info['config'], true);
        return $info;
    }

    public function add($request)
    {
        $rule = $request->input('rule');
        $config = $request->input('config');
        $insert = [
            'name'         => $request->input('name'),
            'start_type'   => $request->input('start_type'),
            'start_method' => $request->input('start_method', 0),
            'start_time'   => $request->input('start_time', ''),
            'rule'         => json_encode($rule, JSON_UNESCAPED_UNICODE),
            'config'       => json_encode($config, JSON_UNESCAPED_UNICODE),
        ];
        return $this->pushModel->insertGetId($insert);
    }

    public function edit($request)
    {
        //todo 标签集合存了用户ID，[倒排表]，使用集合筛选出用户，然后筛选条件
        $id = $request->input('id');
        $status = $this->pushModel->select('status')->whereKey($id)->first();
        if ($status['status'] > 1) return ['msg' => '当前配置已生效，不可修改'];
        $rule = $request->input('rule');
        $config = $request->input('config');
        $update = [
            'name'         => $request->input('name'),
            'start_type'   => $request->input('start_type'),
            'start_method' => $request->input('start_method', 0),
            'start_time'   => $request->input('start_time', ''),
            'rule'         => json_encode($rule, JSON_UNESCAPED_UNICODE),
            'config'       => json_encode($config, JSON_UNESCAPED_UNICODE),
            'status'       => self::DISABLE
        ];
        return $this->pushModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        return $this->pushModel->whereKey($id)->update(['status' => $status]);
    }
}