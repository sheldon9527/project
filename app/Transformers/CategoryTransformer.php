<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Category;

class CategoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['children', 'parent'];

    // 默认的transform，返回对象的所有信息
    public function transform(Category $category)
    {
        if ($parent = $category->parent) {
            $category->parent_name = $parent->getLangAttribute('name');
        }

        if ($category->icon_url) {
            $category->icon_url = $category->getCloudUrl($category->icon_url);
        }

        return $category->attributesToArray();
    }

    public function includeChildren(Category $category)
    {
        if ($childrens = $category->children) {
            return $this->collection($childrens, new self());
        }

        return;
    }

    public function includeParent(Category $category)
    {
        if ($parent = $category->parent) {
            return $this->item($parent, new self());
        }

        return;
    }
}
