<?php

namespace App\Transformers;

use App\Models\Notification;
use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
{
    public function transform(Notification $notification)
    {
        $notification->type = $notification->type;
        $notification->type_id = $notification->type_id;
        if ($extra = $notification->extra) {
            $notification->label = array_key_exists('label', $extra) ? $extra['label'] : null;
        }

        return $notification->attributesToArray();
    }
}
