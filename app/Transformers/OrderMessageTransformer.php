<?php

namespace App\Transformers;

use App\Models\OrderMessage;
use League\Fractal\TransformerAbstract;

class OrderMessageTransformer extends TransformerAbstract
{
    public function transform(OrderMessage $message)
    {
        $message->type = $message->type;

        $message->type_id = $message->type_id;

        return $message->attributesToArray();
    }
}
