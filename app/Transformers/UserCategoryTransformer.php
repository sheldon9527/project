<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Category;

class UserCategoryTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['children'];

    protected $ids;

    public function __construct($ids = [])
    {
        $this->ids = $ids;
    }

    public function transform(Category $category)
    {
        if (\App::getLocale() == 'en') {
            $category->name = $category->en_name;
        }
        $result = $category->attributesToArray();
        unset($result['en_name'], $result['pivot']);

        return $result;
    }

    public function includeChildren(Category $category)
    {
        $children = $category->children();
        if ($this->ids) {
            $children->whereIn('id', $this->ids);
        }

        if ($children->count()) {
            return $this->collection($children->get(), new self($this->ids));
        }

        return;
    }
}
