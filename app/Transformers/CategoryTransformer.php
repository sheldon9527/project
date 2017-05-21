<?php

namespace App\Transformers;

use App\Models\Category;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['children', 'parent'];

    // 默认的transform，返回对象的所有信息
    public function transform(Category $category)
    {
        return $category->attributesToArray();
    }
}
