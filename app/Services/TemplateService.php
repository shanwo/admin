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
        $lists = $this->templateModel->select('id', 'name', 'preview', 'type', 'order', 'updated_at')->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $data['lists'] = $lists->orderBy('order', 'DESC')->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
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
            'type'          => $request->input('type'),
            'head_schema'   => json_encode($request->input('head_schema'), JSON_UNESCAPED_UNICODE),
            'head_data'     => json_encode($request->input('head_data'), JSON_UNESCAPED_UNICODE),
            'center_schema' => json_encode($request->input('center_schema'), JSON_UNESCAPED_UNICODE),
            'center_data'   => json_encode($request->input('center_data'), JSON_UNESCAPED_UNICODE),
            'bottom_schema' => json_encode($request->input('bottom_schema', []), JSON_UNESCAPED_UNICODE),
            'bottom_data'   => json_encode($request->input('bottom_data', []), JSON_UNESCAPED_UNICODE),
        ];
        $id = $this->templateModel->insertGetId($insert);
        $this->redis::HSET('template:' . $insert['sort_id'], $id, json_encode([
            'id' => $id, 'sort_id' => $insert['sort_id'], 'name' => $insert['name'], 'preview' => $insert['preview'], 'type' => $insert['type']
        ], JSON_UNESCAPED_UNICODE));
        return $this->redis::SET('template_infos:' . $id, json_encode($insert, JSON_UNESCAPED_UNICODE));
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
            'type'          => $request->input('type'),
            'head_schema'   => json_encode($request->input('head_schema'), JSON_UNESCAPED_UNICODE),
            'head_data'     => json_encode($request->input('head_data'), JSON_UNESCAPED_UNICODE),
            'center_schema' => json_encode($request->input('center_schema'), JSON_UNESCAPED_UNICODE),
            'center_data'   => json_encode($request->input('center_data'), JSON_UNESCAPED_UNICODE),
            'bottom_schema' => json_encode($request->input('bottom_schema', []), JSON_UNESCAPED_UNICODE),
            'bottom_data'   => json_encode($request->input('bottom_data', []), JSON_UNESCAPED_UNICODE),
        ];
        $this->templateModel->whereKey($id)->update($update);
        $this->redis::HSET('template:' . $update['sort_id'], $id, json_encode([
            'id' => $id, 'sort_id' => $update['sort_id'], 'name' => $update['name'], 'preview' => $update['preview'], 'type' => $update['type']
        ], JSON_UNESCAPED_UNICODE));
        return $this->redis::SET('template_infos:' . $id, json_encode($update, JSON_UNESCAPED_UNICODE));
    }

    public function del($request)
    {
        $id = $request->input('id');
        $this->templateModel->whereKey($id)->update(['is_delete' => 1]);
        $info = $this->templateModel->whereKey($id)->first();
        $this->redis::HDEL('template:' . $info['sort_id'], $id);
        return $this->redis::DEL('template_infos:' . $id);
    }
}
