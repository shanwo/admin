<?php
/**
 * User: Powerless
 * Date: 21/06/20
 * Blog: https://wzxaini9.cn
 */

namespace App\Services;

use App\Models\AreaModel;
use App\Models\AttentionModel;
use App\Models\BrowseModel;
use App\Models\UserModel;
use App\Models\UserThirdModel;
use Illuminate\Support\Facades\Crypt;

class UserService extends Service
{
    protected $areaModel;
    protected $userModel;
    protected $browseModel;
    protected $attentionModel;
    protected $userThirdModel;

    public function __construct()
    {
        parent::__construct();
        $this->areaModel = new AreaModel();
        $this->userModel = new UserModel();
        $this->browseModel = new BrowseModel();
        $this->attentionModel = new AttentionModel();
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
        $info['third'] = $this->userThirdModel->select('logins_number', 'source', 'open_id', 'login_ip', 'updated_at', 'created_at')
            ->where(['user_id' => $id])->get()->toArray();
        return $info;
    }

    public function status($request)
    {
        $uid = $request->input('id');
        $update = [
            'type' => $request->input('type'),
        ];
        return $this->userModel->whereKey($uid)->update($update);
    }
}
