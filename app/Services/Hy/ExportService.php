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
        $where[] = ['e.status', '<>', Service::DELETE];
        $vid = $request->input('venue_id');
        $where[] = ['e.venue_id', '=', $vid];
        $uid = $request->input('user_id');
        if ($uid) $where[] = ['e.user_id', '=', $uid];
        $keyword = $request->input('keyword');
        if ($keyword) $where[] = ['e.name', 'like', "%$keyword%"];
        $start_time = $request->input('start_time');
        if ($start_time) $where[] = ['e.created_at', '>=', $start_time . ' 00:00:00'];
        $end_time = $request->input('end_time');
        if ($end_time) $where[] = ['e.created_at', '<=', $end_time . ' 23:59:59'];
        $page = $request->input('page', $this->page);
        $size = $request->input('size', $this->size);
        $lists = $this->taskModel->from('hy_export as e')
            ->select('e.id', 'e.type', 'venue.name as venue_name', 'e.user_id', 'e.file_name', 'e.file_url', 'e.status', 'e.updated_at', 'e.created_at')
            ->leftjoin('hy_venue as venue', 'venue.id', '=', 'e.venue_id')
            ->where($where);
        $data['page'] = $page;
        $data['size'] = $size;
        $data['total'] = $lists->count();
        $lists = $lists->orderBy('e.id', 'DESC')->forPage($page, $size)->get()->toArray();
        foreach ($lists as &$v) {
            $user = $this->redis::GET('user_infos:' . $v['user_id']);
            $info = json_decode($user, true);
            $v['nickname'] = $info['nickname'] ?? '';
        }
        $data['lists'] = $lists;
        return $data;
    }

    public function export($request,$type)
    {

    }
}
