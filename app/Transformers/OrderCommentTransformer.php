<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\OrderComment;

class OrderCommentTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['user'];
    protected $availableIncludes = ['admin', 'attachments'];

    public function transform(OrderComment $comment)
    {
        return $comment->attributesToArray();
    }

    public function IncludeUser(OrderComment $comment)
    {
        return $this->item($comment->user, new UserTransformer());
    }

    public function IncludeAdmin(OrderComment $comment)
    {
        return $this->item($comment->admin, new AdminTransformer());
    }

    public function includeAttachments(OrderComment $comment)
    {
        return $this->collection($comment->attachments, new AttachmentTransformer());
    }
}
