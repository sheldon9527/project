<?php

namespace App\Transformers;

use App\Models\InquiryOrder;
use App\Models\Questionnaire;
use League\Fractal\TransformerAbstract;

class InquiryOrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'owner',
        'comments',
        'questionnaire',
    ];

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function transform(InquiryOrder $order)
    {
        $result = $order->attributesToArray();

        return $result;
    }

    public function includeOwner(InquiryOrder $order)
    {
        return $this->item($order->owner, new UserTransformer());
    }

    public function includeQuestionnaire(InquiryOrder $order)
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

    public function includeComments(InquiryOrder $order)
    {
        return $this->collection($order->comments()->where('user_id', $this->user->id)->get(), new OrderCommentTransformer());
    }
}
