<?php

namespace App\Transformers;

use App\Models\ServiceOrder;
use League\Fractal\TransformerAbstract;

class ServiceOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'item',
        'details',
        'fristDrafts',
        'finalDrafts',
        'attachments',
        'contract',
    ];

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function transform(ServiceOrder $order)
    {
        if ($order->checkOwner($this->user)) {
            $this->setDefaultIncludes(['contact']);
        }

        if ($order->checkContact($this->user)) {
            $this->setDefaultIncludes(['owner']);
        }

        $extra = $order->extra;
        if ($extra) {
            $order->draft_note = array_key_exists('draft_note', $extra) ? $extra['draft_note'] : null;
            $order->last_note = array_key_exists('last_note', $extra) ? $extra['last_note'] : null;
        }

        unset($order->extra);

        return $order->attributesToArray();
    }

    public function includeItem(ServiceOrder $order)
    {
        if ($order->item) {
            return $this->item($order->item, new ServiceOrderItemTransformer());
        }
    }

    public function includeOwner(ServiceOrder $order)
    {
        return $this->item($order->owner, new UserTransformer());
    }

    public function includeContact(ServiceOrder $order)
    {
        if ($order->contact) {
            return $this->item($order->contact, new DesignerTransformer());
        }
    }

    public function includeContract(ServiceOrder $order)
    {
        if ($contract = $order->contract) {
            $contract->changeContent($order->getContractParams());

            return $this->item($contract, new ContractTransformer());
        }
    }

    public function includeDetails(ServiceOrder $order)
    {
        return $this->collection($order->details, new AttachmentTransformer());
    }

    public function includeFristDrafts(ServiceOrder $order)
    {
        return $this->collection($order->fristDrafts, new AttachmentTransformer());
    }

    public function includeFinalDrafts(ServiceOrder $order)
    {
        return $this->collection($order->finalDrafts, new AttachmentTransformer());
    }
}
