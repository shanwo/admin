<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\UserModel;
use App\Models\UserThirdModel;

class UserService extends Service
{
    protected $userModel;
    protected $browseService;
    protected $attentionService;
    protected $userThirdModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->browseService = new BrowseService();
        $this->attentionService = new AttentionService();
        $this->userThirdModel = new UserThirdModel();
    }

    public function lists($request)
    {
        $where = [];
        $type = $request->input('type');
        if ($type) $where[] = ['type', '=', $type];
        $area = $request->input('area');
        if ($area) $where[] = ['area_id', '=', $area];
        $keyword = $request->input('keyword');
        if ($keyword) {
            $where[] = ['contact', 'like', "%$keyword%"];
            $where[] = ['nickname', 'like', "%$keyword%", 'or'];
        }
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->userModel->select('id', 'type', 'nickname', 'sex', 'avatar', 'merger_name', 'pages', 'contact', 'expired_at', 'updated_at')->where($where);
        $data['total'] = $lists->count();
        $data['page'] = $page;
        $data['size'] = $size;
        $lists = $lists->orderBy('id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => &$v) {
            $v['contact'] = json_decode($v['contact'], true) ?? [];
        }
        $data['lists'] = $lists;
        return $data;
    }

    public function info($request)
    {
        $id = $request->input('id');
        $info = $this->userModel->whereKey($id)->first();
        $info['contact'] = json_decode($info['contact'], true) ?? [];
        $info['third'] = $this->userThirdModel->select('nickname', 'logins_number', 'source', 'open_id', 'login_ip', 'updated_at', 'created_at')
            ->where(['user_id' => $id])->get()->toArray();
        $info['attention'] = $this->attentionService->user($id,$request);
        $info['browse'] = $this->browseService->user($id,$request);
        return $info;
    }

    public function status($request)
    {
        $id = $request->input('id');
        $update = [
            'type' => $request->input('type'),
        ];
        return $this->userModel->whereKey($id)->update($update);
    }
}
