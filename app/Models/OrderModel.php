<?php
/**
 * User: Powerless
 * Date: 21/08/17
 * Blog: https://wzxaini9.cn
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    protected $table;
    public $timestamps = false;

    public function prefix($prefix)
    {
        $this->table = $prefix.'_order';
    }
}
