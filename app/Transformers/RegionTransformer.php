<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Region;

class RegionTransformer extends TransformerAbstract
{
    public function transform(Region $regions)
    {
        if ($regions->children()->count()) {
            $this->setDefaultIncludes(['children']);
        }

        return $regions->attributesToArray();
    }

    public function includeChildren(Region $regions)
    {
        if ($children = $regions->children) {
            return $this->collection($children, new self());
        }

        return;
    }
}
