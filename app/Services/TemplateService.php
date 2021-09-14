<?php
/**
 * User: Powerless
 * Date: 21/06/22
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\TemplateModel;

class TemplateService extends Service
{
    protected $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->templateModel = new TemplateModel();
    }

    public function lists($request)
    {
        $where[] = ['is_delete', '=', 0];
        $sort = $request->input('sort_id');
        if ($sort) $where[] = ['sort_id', '=', $sort];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->templateModel->select('id', 'name', 'preview', 'type', 'order')->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        $data['lists'] = $lists;
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->templateModel->whereKey($id)->first();
        $info['head_schema'] = json_decode($info['head_schema'], true);
        $info['head_data'] = json_decode($info['head_data'], true);
        $info['center_schema'] = json_decode($info['center_schema'], true);
        $info['center_data'] = json_decode($info['center_data'], true);
        $info['bottom_schema'] = json_decode($info['bottom_schema'], true);
        $info['bottom_data'] = json_decode($info['bottom_data'], true);
        return $info;
    }

    public function add($request)
    {
        $insert = [
            'sort_id'       => $request->input('sort_id'),
            'name'          => $request->input('name'),
            'order'         => $request->input('order', 0),
            'preview'       => $request->input('preview'),
            'background'    => $request->input('background'),
            'head_schema'   => json_encode($request->input('head_schema'), JSON_UNESCAPED_UNICODE),
            'head_data'     => json_encode($request->input('head_data'), JSON_UNESCAPED_UNICODE),
            'center_schema' => json_encode($request->input('center_schema'), JSON_UNESCAPED_UNICODE),
            'center_data'   => json_encode($request->input('center_data'), JSON_UNESCAPED_UNICODE),
            'bottom_schema' => json_encode($request->input('bottom_schema', []), JSON_UNESCAPED_UNICODE),
            'bottom_data'   => json_encode($request->input('bottom_data', []), JSON_UNESCAPED_UNICODE),
            'type'          => $request->input('type'),
        ];
        return $this->templateModel->insertGetId($insert);
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'sort_id'       => $request->input('sort_id'),
            'name'          => $request->input('name'),
            'order'         => $request->input('order', 0),
            'preview'       => $request->input('preview'),
            'background'    => $request->input('background'),
            'head_schema'   => json_encode($request->input('head_schema'), JSON_UNESCAPED_UNICODE),
            'head_data'     => json_encode($request->input('head_data'), JSON_UNESCAPED_UNICODE),
            'center_schema' => json_encode($request->input('center_schema'), JSON_UNESCAPED_UNICODE),
            'center_data'   => json_encode($request->input('center_data'), JSON_UNESCAPED_UNICODE),
            'bottom_schema' => json_encode($request->input('bottom_schema', []), JSON_UNESCAPED_UNICODE),
            'bottom_data'   => json_encode($request->input('bottom_data', []), JSON_UNESCAPED_UNICODE),
            'type'          => $request->input('type'),
        ];
        return $this->templateModel->whereKey($id)->update($update);
    }

    public function del($request)
    {
        $id = $request->input('id');
        return $this->templateModel->whereKey($id)->update(['is_delete' => 1]);
    }
}
