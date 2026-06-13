<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Syncables\Traits\Syncable;

class Post extends Model
{
    use Syncable;

    protected $fillable = ['title'];
}
