<?php

namespace App\Transformers;

use App\Models\Questionnaire;
use App\Models\InquiryServiceOrder;
use League\Fractal\TransformerAbstract;

class InquiryServiceOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'contract',
        'fristDrafts',
        'finalDrafts',
        'comments',
        'questionnaire',
    ];

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function transform(InquiryServiceOrder $order)
    {
        if ($order->checkOwner($this->user)) {
            $this->setDefaultIncludes(['contact']);
        }

        if ($order->checkContact($this->user)) {
            $this->setDefaultIncludes(['owner']);
        }

        $type = 'owner';

        if ($this->user->type == 'DESIGNER') {
            $type = 'contract';
        }
        $order->statusLabels = trans('order.inquiry_service_'.$type.'_status.'.$order->status);
        $order->cover_picture_url = $order->getCloudUrl($order->category ? $order->category->icon_url : '');

        if ($this->user->type == 'DESIGNER') {
            $order->amount = $order->contact_amount;
        }

        $result = $order->attributesToArray();

        return $result;
    }

    public function includeOwner(InquiryServiceOrder $order)
    {
        return $this->item($order->owner, new UserTransformer());
    }

    public function includeContact(InquiryServiceOrder $order)
    {
        if ($order->contact) {
            if ($order->contact->type == 'MAKER') {
                $order->contact->factory_name = $order->contact->factory->getLangAttribute('name');

                return $this->item($order->contact, new MakerTransformer());
            } else {
                return $this->item($order->contact, new UserTransformer());
            }
        }
    }

    public function includeFristDrafts(InquiryServiceOrder $order)
    {
        if ($this->user->type == 'DESIGNER') {
            $firstContent = $order->fristDrafts;
        } else {
            $firstContent = $order->defaraFristDrafts;
        }

        return $this->collection($firstContent, new AttachmentTransformer());
    }

    public function includeFinalDrafts(InquiryServiceOrder $order)
    {
        if ($this->user->type == 'DESIGNER') {
            $lastContent = $order->finalDrafts;
        } else {
            $lastContent = $order->defaraFinalDrafts;
        }

        return $this->collection($lastContent, new AttachmentTransformer());
    }

    public function includeComments(InquiryServiceOrder $order)
    {
        return $this->collection($order->comments()->where('user_id', $this->user->id)->get(), new OrderCommentTransformer());
    }

    public function includeContract(InquiryServiceOrder $order)
    {
        if ($contract = $order->contract) {
            if ($this->user->type == 'DESIGNER') {
                $contract->changeContent($order->getContractContactParams());
            } else {
                $contract->changeContent($order->getContractOwnerParams());
            }

            return $this->item($contract, new ContractTransformer());
        }
    }

    public function includeQuestionnaire(InquiryServiceOrder $order)
    {
        if ($item = $order->questionnaireItem) {
            $topics = Questionnaire::find($item->questionnaire_id)->topics;

            $test = [];
            foreach ($topics as $key => $topic) {
                $test[$key]['topic'] = $topic->title;
                $answer = $item->answers()->where('questionnaire_topic_id', $topic->id)->first();
                $test[$key]['answer'] = $answer ? $answer->value : '';
            }

            $item->answer = $test;

            return $this->item($item, new BaseTransformer());
        }
    }
}
