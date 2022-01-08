<?php

namespace App\Services;

use EasyWeChat\Factory;

class OfficialService extends Service
{
    protected $official;

    public function __construct()
    {
        parent::__construct();
        $config = $this->configInfo('official');
        $this->official = Factory::officialAccount($config);
    }

    public function menuInfo()
    {
        return $this->official->menu->list();
    }

    public function menuUpdate($request)
    {
        $button = $request->input('button');
        return $this->official->menu->create($button);
    }

    public function shorten($request)
    {
        $url = $request->input('long_url');
        return $this->official->url->shorten($url);
    }

    public function lists($request)
    {
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $offset = ($page - 1) * $size;
        $type = $request->input('type', 'news');
        $count = $this->official->material->stats();
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $count[$type . '_count'] ?? 0;
        $lists = $this->official->material->list($type, $offset, $size);
        $data['lists'] = $lists['item'];
        return $data;
    }

    public function info($request)
    {
        $mediaId = $request->input('media_id');
        return $this->official->material->get($mediaId);
    }

    public function del($request)
    {
        $mediaId = $request->input('media_id');
        return $this->official->material->delete($mediaId);
    }
}