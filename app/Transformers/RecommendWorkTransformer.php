<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\DesignerWork;

class RecommendWorkTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['user'];

    public function transform(DesignerWork $work)
    {
        if ($work->cover_picture_url) {
            $work->cover_picture_url = url($work->cover_picture_url);
        }

        if ($work->recommend_picture_url) {
            $work->recommend_picture_url = url($work->recommend_picture_url);
        }

        return $work->attributesToArray();
    }

    public function includeUser(DesignerWork $work)
    {
        return $this->item($work->user, new RecommendDesignerTransformer());
    }
}
