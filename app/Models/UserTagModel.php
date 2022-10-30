<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTagModel extends Model
{
    protected $connection = 'share';
    protected $table = 'user_tag';
    public $timestamps = false;
}
