<?php

namespace App\Transformers;

use App\Models\PurchaseOrder;
use League\Fractal\TransformerAbstract;

class PurchaseOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'address',
        'size_table',
        'auxiliary_datas',
        'category',
        'contract',
    ];

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
    public function transform(PurchaseOrder $order)
    {
        if ($order->checkOwner($this->user)) {
            $this->setDefaultIncludes(['contact']);
        }

        if ($order->checkContact($this->user)) {
            $this->setDefaultIncludes(['owner']);
        }

        $result = $order->attributesToArray();

        if (in_array($order->status, ['OWNER_CANCELED', 'CONTACT_CANCELED'])) {
            $result['status'] = 'CANCELED';
        }

        return $result;
    }

    public function includeOwner(PurchaseOrder $order)
    {
        return $this->item($order->owner, new UserTransformer());
    }

    public function includeContact(PurchaseOrder $order)
    {
        if ($order->contact) {
            // TODO 虽然不是很想这么写
            $order->contact->factory_name = $order->contact->factory->getLangAttribute('name');

            return $this->item($order->contact, new MakerTransformer());
        }
    }

    public function includeAddress(PurchaseOrder $order)
    {
        return $this->item($order->address, new OrderAddressTransformer());
    }

    public function includeSizeTable(PurchaseOrder $order)
    {
        $size_table = $order->attachments()->where('tag', 'size_table')->first();
        if ($size_table) {
            return $this->item($size_table, new AttachmentTransformer());
        }
    }

    public function includeAuxiliaryDatas(PurchaseOrder $order)
    {
        $auxiliaryDatas = $order->attachments()->where('tag', 'auxiliary_datas')->get();

        if ($auxiliaryDatas->count()) {
            return $this->collection($auxiliaryDatas, new AttachmentTransformer());
        }
    }

    public function includeCategory(PurchaseOrder $order)
    {
        if ($order->category) {
            return $this->item($order->category, new CategoryTransformer());
        }
    }

    public function includeContract(PurchaseOrder $order)
    {
        if ($contract = $order->contract) {
            $contract->changeContent($order->toArray());

            return $this->item($contract, new ContractTransformer());
        }
    }
}
