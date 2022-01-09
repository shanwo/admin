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
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['b.page_id', '=', $id];
        $lists = $this->browseModel->from('sw_browse as b')
            ->select('u.id', 'u.avatar', 'u.nickname', 'b.click')
            ->leftJoin('sw_user as u', 'b.user_id', '=', 'u.id')
            ->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $data['lists'] = $lists->orderBy('b.id', 'DESC')->forPage($page, $size)->get()->toArray();
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
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['b.user_id', '=', $id];
        $lists = $this->browseModel->from('sw_browse as b')
            ->select('b.page_id', 'p.head_data', 'b.click')
            ->leftJoin('sw_page as p', 'b.page_id', '=', 'p.id')
            ->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('b.id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $info = json_decode($v['head_data'], true);
            $v = [
                'id'       => $v['page_id'],
                'avatar'   => $info['avatar'],
                'click'    => $info['click'],
                'nickname' => $info['nickname']
            ];
        }
        $data['lists'] = $lists;
        return $data;
    }
}
