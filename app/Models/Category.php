<?php

namespace App\Models;

use Baum\Node;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Node
{
    use SoftDeletes;

    // 'parent_id' column name
    protected $parentColumn = 'parent_id';

    // 'lft' column name
    protected $leftColumn = 'left';

    // 'rgt' column name
    protected $rightColumn = 'right';

    // 'depth' column name
    protected $depthColumn = 'level';

    protected $dates = ['deleted_at'];

    protected $orderColumn = 'left';

    protected $guarded = array('id', 'parent_id', 'left', 'right', 'level');

    public $tree = null;

    protected $hidden = [
        'created_at',
        'updated_at',
        'status',
        'deleted_at',
        'order_number',
        'level',
        'right',
        'left',
        'en_name',
        'icon_url',
        'parent_id'
    ];

    protected $langFields = [
        'name',
    ];
}
