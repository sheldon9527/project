<?php

namespace App\Transformers;

use App\Models\ProductionOrder;
use League\Fractal\TransformerAbstract;

class ProductionOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'contract',
        'comments',
        'packOrder',
        'invoice',
    ];

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function transform(ProductionOrder $order)
    {
        if ($order->checkOwner($this->user)) {
            $this->setDefaultIncludes(['contact']);
        }

        if ($order->checkContact($this->user)) {
            $this->setDefaultIncludes(['owner']);
        }

        if ($this->user->type == 'MAKER') {
            $order->owner_order_name = $order->contact_order_name;
            $order->amount = $order->contact_amount;
        }

        return $order->attributesToArray();
    }

    public function includeOwner(ProductionOrder $order)
    {
        return $this->item($order->owner, new UserTransformer());
    }

    public function includeContact(ProductionOrder $order)
    {
        if ($order->contact) {
            return $this->item($order->contact, new MakerTransformer());
        }

        return;
    }

    public function includeContract(ProductionOrder $order)
    {
        if ($contract = $order->contract) {
            if ($this->user->type == 'MAKER') {
                $contract->changeContent($order->getContractContactParams());
            } else {
                $contract->changeContent($order->getContractOwnerParams());
            }

            return $this->item($contract, new ContractTransformer());
        }
    }

    public function includeComments(ProductionOrder $order)
    {
        return $this->collection($order->comments()->where('user_id', $this->user->id)->get(), new OrderCommentTransformer());
    }

    public function includeInvoice(ProductionOrder $order)
    {
        $invoice = $order->attachments()->where('tag', 'invoice')->get();
        if ($invoice) {
            return $this->collection($invoice, new AttachmentTransformer());
        }

        return;
    }

    public function includePackOrder(ProductionOrder $order)
    {
        $packOrder = $order->attachments()->where('tag', 'pack_order')->get();
        if ($packOrder) {
            return $this->collection($packOrder, new AttachmentTransformer());
        }

        return;
    }
}
