<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\ServiceOrderItem;

class ServiceOrderItemTransformer extends TransformerAbstract
{
    public function transform(ServiceOrderItem $item)
    {
        if ($item->cover_picture_url) {
            $item->cover_picture_url = url($item->cover_picture_url);
        }

        return $item->attributesToArray();
    }
}
