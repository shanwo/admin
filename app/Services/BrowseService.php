<?php
/**
 * User: Powerless
 * Date: 2021/7/11
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\BrowseModel;

class BrowseService extends Service
{
    protected $browseModel;

    public function __construct()
    {
        parent::__construct();
        $this->browseModel = new BrowseModel();
    }

    /**
     * 页面浏览记录
     * @param $id       闪页ID
     * @param $request  请求参数
     * @return mixed
     */
    public function page($id, $request)
    {
        $page = $request->input('browse_page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['page_id', '=', $id];
        $lists = $this->browseModel->select('user_id')->where($where);
        $data['total'] = $lists->count();
        $data['browse_page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $info = $this->redis::GET('list_pages:' . $v['user_id']);
            if ($info) {
                $info = json_decode($info, true);
                $v = ['id' => $info['id'], 'avatar' => $info['avatar'], 'nickname' => $info['nickname']];
            } else {
                $v = ['id' => $v['page_id'], 'avatar' => '', 'nickname' => '未查到数据'];
            }
        }
        $data['lists'] = $lists;
        return $data;
    }

    /**
     * 用户浏览记录
     * @param $id       用户ID
     * @param $request  请求参数
     * @return mixed
     */
    public function user($id, $request)
    {
        $page = $request->input('browse_page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['user_id', '=', $id];
        $lists = $this->browseModel->select('page_id')->where($where);
        $data['total'] = $lists->count();
        $data['browse_page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $info = $this->redis::GET('user_infos:' . $v['page_id']);
            if ($info) {
                $info = json_decode($info, true);
                $v = ['id' => $info['id'], 'avatar' => $info['avatar'], 'nickname' => $info['nickname']];
            } else {
                $v = ['id' => $v['page_id'], 'avatar' => '', 'nickname' => '未查到数据'];
            }
        }
        $data['lists'] = $lists;
        return $data;
    }
}
