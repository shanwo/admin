<?php
/**
 * User: Powerless
 * Date: 21/08/09
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Sw;

use App\Models\Sw\PayModel;
use App\Services\Service;

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
        $where[] = ['status', '<>', Service::DELETE];
        $channel = $request->input('channel', 0);
        if ($channel) $where[] = ['channel', '=', $channel];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->payModel->select('id', 'name', 'channel', 'original_price', 'activity_price', 'description', 'type', 'status', 'created_at')->where($where);
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
        $insert = [
            'name'           => $request->input('name'),
            'original_price' => $request->input('original_price') * 100,
            'activity_price' => $request->input('activity_price') * 100,
            'description'    => $request->input('description', ''),
            'channel'        => $request->input('channel', 0),
            'type'           => $request->input('type'),
        ];
        return $this->payModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'name'           => $request->input('name'),
            'original_price' => $request->input('original_price') * 100,
            'activity_price' => $request->input('activity_price') * 100,
            'description'    => $request->input('description', ''),
            'channel'        => $request->input('channel', 0),
            'type'           => $request->input('type'),
            'status'         => self::DISABLE
        ];
        return $this->payModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $info = $this->payModel->whereKey($id)->first();
        $status = $request->input('status');
        if ($status == 1) {
            $this->redis::HSET('pay_channel:' . $info['channel'], $id, json_encode([
                'id' => $id, 'name' => $info['name'], 'channel' => $info['channel'], 'original_price' => $info['original_price'], 'activity_price' => $info['activity_price'], 'description' => $info['description'], 'type' => $info['type']
            ], JSON_UNESCAPED_UNICODE));
        } else {
            $this->redis::HDEL('pay_channel:' . $info['channel'], $id);
        }
        return $this->payModel->whereKey($id)->update(['status' => $status]);
    }
}
