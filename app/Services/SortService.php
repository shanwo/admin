<?php
/**
 * User: Powerless
 * Date: 2021/7/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\DecorationModel;
use App\Models\LinkModel;
use App\Models\SortModel;
use App\Models\TemplateModel;
use App\Models\TutorialsModel;

class SortService extends Service
{
    protected $sortModel;
    protected $decorationModel;
    protected $tutorialsModel;
    protected $templateModel;
    protected $linkModel;


    public function __construct()
    {
        parent::__construct();
        $this->sortModel = new SortModel();
        $this->decorationModel = new DecorationModel();
        $this->tutorialsModel = new TutorialsModel();
        $this->templateModel = new TemplateModel();
        $this->linkModel = new LinkModel();
    }

    public function lists($request)
    {
        $where = [];
        $type = $request->input('type');
        if ($type) $where[] = ['type', '=', $type];
        $data['lists'] = $this->sortModel->select('id', 'name', 'order', 'type')
            ->where($where)
            ->orderBy('order', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->toArray();
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        return $this->sortModel->whereKey($id)->first();
    }

    public function add($request)
    {
        $insert = [
            'name'  => $request->input('name'),
            'type'  => $request->input('type'),
            'order' => $request->input('order', 0),
        ];
        $id = $this->sortModel->insertGetId($insert);
        return $this->redis::HSET('sort_type:' . $insert['type'], $id, json_encode([
            'id' => $id, 'name' => $insert['name'], 'type' => $insert['type']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function edit($request)
    {
        $id = $request->input('id');
        $update = [
            'name'  => $request->input('name'),
            'order' => $request->input('order', 0),
        ];
        $this->sortModel->whereKey($id)->update($update);
        $info = $this->sortModel->whereKey($id)->first();
        return $this->redis::HSET('sort_type:' . $info['type'], $id, json_encode([
            'id' => $id, 'name' => $info['name'], 'type' => $info['type']
        ], JSON_UNESCAPED_UNICODE));
    }

    public function del($request)
    {
        $id = $request->input('id');
        $this->sortModel->whereKey($id)->delete();
        $info = $this->sortModel->whereKey($id)->first();
        return $this->redis::HDEL('sort_type:' . $info['type'], $id);
    }

    public function checkTotal($info)
    {
        $where = [
            'sort_id' => $info['id'],
        ];
        switch ($info['type']) {
            case 1:
                $lists = $this->decorationModel->where($where)->count();
                break;
            case 2:
                $lists = $this->tutorialsModel->where($where)->count();
                break;
            case 3:
                $lists = $this->templateModel->where($where)->count();
                break;
            case 4:
                $lists = $this->linkModel->where($where)->count();
                break;
        }
        return $lists['total'] ?? 0;
    }
}
