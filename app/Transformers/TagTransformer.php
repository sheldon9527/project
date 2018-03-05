<?php

namespace App\Transformers;

use App\Models\Tags;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['children', 'parent'];

    // 默认的transform，返回对象的所有信息
    public function transform(Tags $tag)
    {
        return $tag->attributesToArray();
    }
}
