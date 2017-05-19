<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Attachment;

class AttachmentTransformer extends TransformerAbstract
{
    public function transform(Attachment $attachment)
    {
        $attachment->relative_path = $attachment->getCloudUrl($attachment->relative_path);

        return $attachment->attributesToArray();
    }
}
