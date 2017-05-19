<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\FactoryContent;

class FactoryContentTransformer extends TransformerAbstract
{
    public function transform(FactoryContent $content)
    {
        if ($content->cover_picture_url) {
            $content->cover_picture_url = $content->getCloudUrl($content->cover_picture_url);
        }

        return $content->attributesToArray();
    }
}
