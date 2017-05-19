<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Category;

class InquiryServiceCategoryTransformer extends TransformerAbstract
{
    public function transform(Category $category)
    {
        if ($category->icon_url) {
            $category->icon_url = $category->getCloudUrl($category->icon_url);
        }

        return $category->attributesToArray();
    }
}
