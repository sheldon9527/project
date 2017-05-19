<?php

namespace App\Transformers;

use App\Models\SampleOrder;
use League\Fractal\TransformerAbstract;

class SampleOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'contract',
        'comments',
        'records',
    ];

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
    public function transform(SampleOrder $order)
    {
        if ($order->checkOwner($this->user)) {
            $this->setDefaultIncludes(['contact']);
        }

        if ($order->checkContact($this->user)) {
            $this->setDefaultIncludes(['owner']);
        }

        $type = 'owner';

        if ($this->user->type == 'MAKER') {
            $type = 'contact';
        }
        $order->statusLabels = trans('order.sample_'.$type.'_status.'.$order->status);
        $order->cover_picture_url = $order->getCloudUrl($order->cover_picture_url);
        if ($this->user->type == 'MAKER') {
            $order->owner_order_name = $order->contact_order_name;
            $order->amount = $order->contact_amount;
        }

        return $order->attributesToArray();
    }

    public function includeOwner(SampleOrder $order)
    {
        return $this->item($order->owner, new UserTransformer());
    }

    public function includeContact(SampleOrder $order)
    {
        if ($order->contact) {
            // // TODO 虽然不是很想这么写
            // $order->contact->factory_name = $order->contact->factory->getLangAttribute('name');

            return $this->item($order->contact, new MakerTransformer());
        }

        return;
    }

    public function includeContract(SampleOrder $order)
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

    public function includeRecords(SampleOrder $order)
    {
        if ($this->user->type == 'MAKER') {
            return $this->collection($order->records()->where('sample_order_records.type', '=', 'maker')->get(), new SampleOrderRecordTransformer());
        } else {
            return $this->collection($order->records()->where('sample_order_records.type', '=', 'defara')->get(), new SampleOrderRecordTransformer());
        }
    }

    public function includeComments(SampleOrder $order)
    {
        return $this->collection($order->comments()->where('user_id', $this->user->id)->get(), new OrderCommentTransformer());
    }
}
