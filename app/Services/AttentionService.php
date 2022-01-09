<?php
/**
 * User: Powerless
 * Date: 2021/7/10
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\AttentionModel;

class AttentionService extends Service
{
    protected $attentionModel;

    public function __construct()
    {
        parent::__construct();
        $this->attentionModel = new AttentionModel();
    }

    /**
     * 页面关注记录
     * @param $id       闪页ID
     * @param $request  请求参数
     * @return mixed
     */
    public function page($id, $request)
    {
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['a.page_id', '=', $id];
        $lists = $this->attentionModel->from('sw_attention as a')
            ->select('u.id', 'u.avatar', 'u.nickname', 'a.status')
            ->leftJoin('sw_user as u', 'a.user_id', '=', 'u.id')
            ->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $data['lists'] = $lists->orderBy('a.id', 'DESC')->forPage($page, $size)->get()->toArray();
        return $data;
    }

    /**
     * 用户关注记录
     * @param $id       用户ID
     * @param $request  请求参数
     * @return mixed
     */
    public function user($id, $request)
    {
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['a.status', 'in', [1, 2, 3]];
        $where[] = ['a.user_id', '=', $id];
        $lists = $this->attentionModel->from('sw_attention as a')
            ->select('a.page_id', 'p.head_data', 'a.status')
            ->leftJoin('sw_page as p', 'a.page_id', '=', 'p.id')
            ->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('a.id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $info = json_decode($v['head_data'], true);
            $v = [
                'id'       => $v['page_id'],
                'avatar'   => $info['avatar'],
                'nickname' => htmlentities($info['nickname'])
            ];
        }
        $data['lists'] = $lists;
        return $data;
    }
}
