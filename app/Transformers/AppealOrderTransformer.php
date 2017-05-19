<?php

namespace App\Transformers;

use App\Models\AppealOrder;
use League\Fractal\TransformerAbstract;

class AppealOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['attachments', 'orginalOrder'];

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function transform(AppealOrder $order)
    {
        if ($order->checkOwner($this->user)) {
            $this->setDefaultIncludes(['contact']);
        }

        if ($order->checkContact($this->user)) {
            $this->setDefaultIncludes(['owner']);
        }

        $order->cover_picture_url = $order->appealable->cover_picture_url;

        return $order->attributesToArray();
    }

    public function includeOwner(AppealOrder $order)
    {
        return $this->item($order->owner, new UserTransformer());
    }

    public function includeContact(AppealOrder $order)
    {
        if ($order->contact) {
            // TODO 虽然不是很想这么写
            $factory = $order->contact->factory;
            $order->contact->factory_name = (\App::getLocale() == 'zh') ? $factory->name : $factory->en_name;

            return $this->item($order->contact, new MakerTransformer());
        }

        return;
    }

    public function includeAttachments(AppealOrder $order)
    {
        return $this->collection($order->attachments, new AttachmentTransformer());
    }
}
