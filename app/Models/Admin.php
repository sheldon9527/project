<?php

namespace App\Models;

class Admin extends BaseModel
{

    protected $hidden = [
        'extra',
        'password',
        'deleted_at',
    ];

    protected $appends = ['avatar'];

    protected $casts = [
        'extra' => 'array',
    ];

    public $statusLabel = [
        'ACTIVE'   => '已激活',
        'INACTIVE' => '未激活',
    ];

    public function getAvatarAttribute()
    {
        return url(config('image.defaultImg'));
    }
}
