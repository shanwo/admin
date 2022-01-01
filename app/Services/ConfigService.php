<?php
/**
 * User: Powerless
 * Date: 21/09/13
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\ConfigModel;

class ConfigService extends Service
{
    protected $configModel;

    public function __construct()
    {
        parent::__construct();
        $this->configModel = new ConfigModel();
    }

    public function lists($request)
    {
        $where[] = ['is_delete', '=', Service::DELETE_NO];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->configModel->select('id', 'autoload', 'option_name', 'created_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->configModel->whereKey($id)->first();
        $info['option_value'] = json_decode($info['option_value'], true);
        return $info;
    }

    public function add($request)
    {
        $insert = [
            'option_name'  => $request->input('option_name'),
            'option_value' => json_encode($request->input('option_value')),
            'autoload'     => $request->input('autoload', 0),
        ];
        return $this->configModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'option_name'  => $request->input('option_name'),
            'option_value' => json_encode($request->input('option_value')),
            'autoload'     => $request->input('autoload', 0),
        ];
        return $this->configModel->whereKey($id)->update($update);
    }

    public function status($request)
    {
        $id = $request->input('id');
        $info = $this->configModel->whereKey($id)->first();
        $status = $request->input('status');
        if ($status == 1) {
            $this->redis::SET('config:' . $info['option_name'], $info['option_value']);
        } else {
            $this->redis::DEL('config:' . $info['option_name']);
        }
        return $this->configModel->whereKey($id)->update(['status' => $status]);
    }
}
