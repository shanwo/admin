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
        $where[] = ['is_delete', '=', 0];
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

    public function add($request)
    {
        $description = $request->input('description');
        if(!$description)  $description = '';
        $channel = $request->input('channel');
        if(!$channel)  $channel = 0;
        $insert = [
            'name'           => $request->input('name'),
            'original_price' => $request->input('original_price') * 100,
            'activity_price' => $request->input('activity_price') * 100,
            'description'    => $description,
            'channel'        => $channel,
            'type'           => $request->input('type'),
        ];
        return $this->payModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $description = $request->input('description');
        if(!$description)  $description = '';
        $channel = $request->input('channel');
        if(!$channel)  $channel = 0;
        $update = [
            'name'           => $request->input('name'),
            'original_price' => $request->input('original_price') * 100,
            'activity_price' => $request->input('activity_price') * 100,
            'description'    => $description,
            'channel'        => $channel,
            'type'           => $request->input('type'),
        ];
        return $this->payModel->whereKey($id)->update($update);
    }

    public function del($request)
    {
        $id = $request->input('id');
        return $this->payModel->whereKey($id)->update(['is_delete' => 1]);
    }
}
