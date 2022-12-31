<?php
/**
 * User: Powerless
 * Date: 23/01/01
 * Blog: https://wzxaini9.cn
 */

namespace App\Models\Hy;

class ExportModel extends HyModel
{
    const EXPORT_CREATE    = 10;
    const EXPORT_CANCEL    = 20;
    const EXPORT_EXECUTION = 30;
    const EXPORT_SUCCESS   = 40;
    const EXPORT_FAIL      = 50;
    protected $table = 'hy_task';
}
