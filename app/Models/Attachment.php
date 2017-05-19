<?php

namespace App\Models;

//使用了软删除
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id', 'user_id'];

    protected $hidden = ['deleted_at', 'attachable_type', 'attachable_id'];

    public function attachable()
    {
        return $this->morphTo();
    }
}
