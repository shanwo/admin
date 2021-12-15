<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\PayModel;

class PayService extends Service
{
    protected $payModel;

    public function __construct()
    {
        parent::__construct();
        $this->payModel = new PayModel();
    }

    public function lists($request)
    {
        $where[] = ['is_delete', '=', Service::DELETE_NO];
        $channel = $request->input('channel', 0);
        if ($channel) $where[] = ['channel', '=', $channel];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->payModel->select('id', 'name', 'channel', 'original_price', 'activity_price', 'description', 'type', 'created_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $v['original_price'] = $v['original_price'] / 100;
            $v['activity_price'] = $v['activity_price'] / 100;
        }
        $data['lists'] = $lists;
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->payModel->whereKey($id)->first();
        $info['original_price'] = $info['original_price'] / 100;
        $info['activity_price'] = $info['activity_price'] / 100;
        return $info;
    }

    public function add($request)
    {
        $description = $request->input('description');
        if (!$description) $description = '';
        $channel = $request->input('channel');
        if (!$channel) $channel = 0;
        $insert = [
            'name'           => $request->input('name'),
            'original_price' => $request->input('original_price') * 100,
            'activity_price' => $request->input('activity_price') * 100,
            'description'    => $description,
            'channel'        => $channel,
            'type'           => $request->input('type'),
        ];
        $id = $this->payModel->insertGetId($insert);
        return $this->redis::HSET('pay_channel:' . $channel, $id, json_encode([
            'id' => $id, 'name' => $insert['name'], 'channel' => $insert['channel'], 'original_price' => $insert['original_price'], 'activity_price' => $insert['activity_price'], 'description' => $insert['description'], 'type' => $insert['type']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $description = $request->input('description');
        if (!$description) $description = '';
        $channel = $request->input('channel');
        if (!$channel) $channel = 0;
        $update = [
            'name'           => $request->input('name'),
            'original_price' => $request->input('original_price') * 100,
            'activity_price' => $request->input('activity_price') * 100,
            'description'    => $description,
            'channel'        => $channel,
            'type'           => $request->input('type'),
        ];
        $this->payModel->whereKey($id)->update($update);
        return $this->redis::HSET('pay_channel:' . $channel, $id, json_encode([
            'id' => $id, 'name' => $update['name'], 'channel' => $update['channel'], 'original_price' => $update['original_price'], 'activity_price' => $update['activity_price'], 'description' => $update['description'], 'type' => $update['type']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function del($request)
    {
        $id = $request->input('id');
        $this->payModel->whereKey($id)->update(['is_delete' => Service::DELETE_YES]);
        $info = $this->payModel->whereKey($id)->first();
        return $this->redis::HDEL('pay_channel:' . $info['channel'], $id);
    }
}
