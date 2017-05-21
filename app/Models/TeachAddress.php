<?php

namespace App\Models;

use App\Traits\Attachment as AttachmentTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachAddress extends BaseModel
{
    use SoftDeletes, AttachmentTrait;

    protected $hidden = [
        'deleted_at',
        'geohash',
        'type'
    ];

    protected $fillable = [
        'category_id',
        'name',
        'address',
        'telephone',
        'geohash',
        'description',
        'latitude',
        'longitude',
        'status',
        'special',
    ];

    protected $appends = ['cover_picture'];

    public function attachments()
    {
        return $this->morphMany('App\Models\Attachment', 'attachable');
    }

    public function getCoverPictureAttribute()
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

    /**
     *  @desc 根据两点间的经纬度计算距离
     *  @param float $latitude 纬度值
     *  @param float $longitude 经度值
     */
    public static function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $earth_radius = 6371000; //approximate radius of earth in meters
        $dLat         = deg2rad($latitude2 - $latitude1);
        $dLon         = deg2rad($longitude2 - $longitude1);
        $a            = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
        $c            = 2 * asin(sqrt($a));
        $d            = $earth_radius * $c;

        return round($d); //四舍五入
    }
}
