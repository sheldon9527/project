<?php

namespace App\Models;

use App\Traits\Attachment as AttachmentTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachAddress extends BaseModel
{
    use SoftDeletes, AttachmentTrait;

    protected $hidden = [
        'deleted_at',
    ];

    protected $fillable = [
        'category_id',
        'address',
        'telephone',
        'geohash',
        'description',
        'status',
        'special',
    ];

    protected $appends = ['cover_picture'];

    public function attachments()
    {
        return $this->morphMany('App\Models\Attachment', 'attachable');
    }

    public function getCoverPIctureAttribute()
    {
        $attachment = $this->attachments->first();
        if ($attachment) {
            return $attachment->relative_path;
        }
        return null;
    }

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function tags()
    {
        return $this->hasMany('App\Models\Tags');
    }
}
