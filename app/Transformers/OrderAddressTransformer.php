<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\OrderAddress;

class OrderAddressTransformer extends TransformerAbstract
{
    public function transform(OrderAddress $address)
    {
        return $address->attributesToArray();
    }
}
