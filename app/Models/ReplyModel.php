<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplyModel extends Model
{
    protected $connection = 'share';
    protected $table = 'reply';
    public $timestamps = false;
}
