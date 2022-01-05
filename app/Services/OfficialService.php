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

    public function menuUpdate($button)
    {
        return $this->official->menu->create($button);
    }
}