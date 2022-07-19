<?php

namespace App\Services;

use EasyWeChat\OfficialAccount\Application;

class OfficialService extends Service
{
    protected $official;

    public function __construct()
    {
        parent::__construct();
        $config = $this->configInfo('official');
        $this->official = new Application($config);
    }

    public function menuInfo()
    {
        return $this->official->getClient()->get('/cgi-bin/menu/get')->toArray();
    }

    public function menuUpdate($request)
    {
        $button = $request->input('button');
        return $this->official->getClient()->postJson('cgi-bin/menu/create', ['button'=>$button])->toArray();
    }

    public function lists($request)
    {
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $offset = ($page - 1) * $size;
        $type = $request->input('type', 'news');
        $count = $this->official->getClient()->get('cgi-bin/material/get_materialcount')->toArray();
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $count[$type . '_count'] ?? 0;
        $lists = $this->official->getClient()->postJson('cgi-bin/material/batchget_material', [
            'type'=>$type,
            'offset'=>$offset,
            'count'=>$size
        ])->toArray();
        $data['lists'] = $lists['item'];
        return $data;
    }

    public function info($request)
    {
        $mediaId = $request->input('media_id');
        return $this->official->getClient()->postJson('cgi-bin/material/get_material', ['media_id'=>$mediaId])->toArray();
    }

    public function del($request)
    {
        $mediaId = $request->input('media_id');
        return $this->official->getClient()->postJson('cgi-bin/material/del_material', ['media_id'=>$mediaId])->toArray();
    }
}