<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\RechargeOrder;

class RechargeOrderTransformer extends TransformerAbstract
{
    public function transform(RechargeOrder $order)
    {
        return $order->attributesToArray();
    }
}
