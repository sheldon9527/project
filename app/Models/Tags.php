<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Tags extends BaseModel
{
    use SoftDeletes;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
