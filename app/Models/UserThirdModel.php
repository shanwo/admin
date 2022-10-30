<?php
/**
 * User: Powerless
 * Date: 21/07/16
 * Blog: https://wzxaini9.cn
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserThirdModel extends Model
{
    protected $connection = 'share';
    protected $table = 'user_third';
    public $timestamps = false;
}
