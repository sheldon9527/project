<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\WithdrawOrder;

class WithdrawOrderTransformer extends TransformerAbstract
{
    public function transform(WithdrawOrder $order)
    {
        $extra = $order->extra;
        if (isset($extra['reason'])) {
            $order->reason = \App::getLocale() == 'zh' ? $extra['reason'] : $extra['en_reason'];
        }

        return $order->attributesToArray();
    }
}
