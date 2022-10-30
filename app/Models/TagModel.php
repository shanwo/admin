<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagModel extends Model
{
    protected $connection = 'share';
    protected $table = 'tag';
    public $timestamps = false;
}
