<?php
/**
 * User: Powerless
 * Date: 2021/7/11
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Sw;

use App\Models\Sw\BrowseModel;
use App\Services\Service;

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
        $where[] = ['page_id', '=', $id];
        $lists = $this->browseModel->select('user_id', 'click')->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => $v) {
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user, true);
            $v['avatar'] = $info['avatar'] ?? 'https://cdn.shanwo.cc/image/avatar.jpg';
            $v['nickname'] = $info['nickname'] ?? '';
            $data['lists'][] = $v;
        }
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
                'click'    => $v['click'],
                'avatar'   => $info['avatar'],
                'nickname' => $info['nickname']
            ];
        }
        $data['lists'] = $lists;
        return $data;
    }
}
