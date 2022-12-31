<?php
/**
 * User: Powerless
 * Date: 23/01/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Services\Hy;

use App\Models\Hy\ExportModel;
use App\Services\Service;

class ExportService extends Service
{
    protected $taskModel;

    public function __construct()
    {
        parent::__construct();
        $this->taskModel = new ExportModel();
    }

    public function lists($request)
    {
        $where[] = ['t.status', '<>', Service::DELETE];
        $vid = $request->input('venue_id');
        $where[] = ['t.venue_id', '=', $vid];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['t.user_id', '=', $uid];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['t.name', 'like', "%$keyword%"];
        $start_time = $request->input('start_time');
        if ($start_time) $where[] = ['t.created_at', '>=', $start_time . ' 00:00:00'];
        $end_time = $request->input('end_time');
        if ($end_time) $where[] = ['t.created_at', '<=', $end_time . ' 23:59:59'];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->taskModel->from('hy_upload as u')
            ->select('t.id', 't.type', 'venue.name as venue_name', 't.user_id', 't.file_name', 't.file_url', 't.status', 't.updated_at', 't.created_at')
            ->leftjoin('hy_venue as venue', 'venue.id', '=', 't.venue_id')
            ->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('t.id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as $k => $v) {
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user, true);
            $v['nickname'] = $info['nickname'] ?? '';
            $data['lists'][] = $v;
        }
        return $data;
    }

    public function export($request,$type)
    {

    }
}
