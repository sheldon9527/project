<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\UserBrand;

class BrandTransformer extends TransformerAbstract
{
    public function transform(UserBrand $band)
    {
        return $band->attributesToArray();
    }
}
