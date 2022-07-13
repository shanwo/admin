<?php

namespace App\Services;

use EasyWeChat\OfficialAccount\Application;

class OfficialService extends Service
{
    protected $official;
    protected $accessToken;

    public function __construct()
    {
        parent::__construct();
        $config = $this->configInfo('official');
        $this->official = new Application($config);
        $this->accessToken = $this->official->getAccessToken();
    }

    public function menuInfo()
    {
        return $this->official->getClient()->get('/cgi-bin/menu/get', ['json' => [
                'access_token' => $this->accessToken,
            ]])->toArray();
    }

    public function menuUpdate($request)
    {
        $button = $request->input('button');
        return $this->official->getClient()->post('cgi-bin/menu/create', ['json' => [
            'access_token' => $this->accessToken,
            'button'=>$button
        ]])->toArray();
    }

    public function lists($request)
    {
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $offset = ($page - 1) * $size;
        $type = $request->input('type', 'news');
        $count = $this->official->getClient()->get('cgi-bin/material/get_materialcount', ['json' => [
            'access_token' => $this->accessToken,
        ]])->toArray();
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $count[$type . '_count'] ?? 0;
        $lists = $this->official->getClient()->post('cgi-bin/material/batchget_material', ['json' => [
            'access_token' => $this->accessToken,
            'type'=>$type,
            'offset'=>$offset,
            'count'=>$size
        ]])->toArray();
        $data['lists'] = $lists['item'];
        return $data;
    }

    public function info($request)
    {
        $mediaId = $request->input('media_id');
        return $this->official->getClient()->post('cgi-bin/material/get_material', ['json' => [
            'access_token' => $this->accessToken,
            'media_id'=>$mediaId,
        ]])->toArray();
    }

    public function del($request)
    {
        $mediaId = $request->input('media_id');
        return $this->official->getClient()->post('cgi-bin/material/del_material', ['json' => [
            'access_token' => $this->accessToken,
            'media_id'=>$mediaId,
        ]])->toArray();
    }
}