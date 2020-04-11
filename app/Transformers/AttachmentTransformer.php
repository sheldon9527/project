<?php

namespace App\Transformers;

use App\Models\Attachment;
use League\Fractal\TransformerAbstract;

class AttachmentTransformer extends TransformerAbstract
{
    public function transform(Attachment $attachment)
    {
        if ($attachment->relative_path) {
            $attachment->relative_path = \Config::get('domains.domain') . $attachment->relative_path;
        }
        return $attachment->attributesToArray();
    }
}
