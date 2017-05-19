<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Region;

class CountryTransformer extends TransformerAbstract
{
    public function transform(Region $country)
    {
        if ($country->iso2) {
            $country->iso2 = strtolower($country->iso2);
        }

        return $country->attributesToArray();
    }
}
