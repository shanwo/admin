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
        $page = $request->input('attention_page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['page_id', '=', $id];
        $lists = $this->attentionModel->select('user_id')->where($where);
        $data['total'] = $lists->count();
        $data['attention_page'] = $page;
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
     * 用户关注记录
     * @param $id       用户ID
     * @param $request  请求参数
     * @return mixed
     */
    public function user($id, $request)
    {
        $page = $request->input('attention_page', $this->page);
        $size = $request->input('size', $this->size);
        $where[] = ['status', '<>', 0];
        $where[] = ['user_id', '=', $id];
        $lists = $this->attentionModel->select('page_id')->where($where);
        $data['total'] = $lists->count();
        $data['attention_page'] = $page;
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
