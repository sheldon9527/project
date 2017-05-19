<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Showroom;

class ShowroomTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['user'];

    protected $availableIncludes = ['works', 'services'];

    public function transform(Showroom $showroom)
    {
        return $showroom->attributesToArray();
    }

    public function includeUser(Showroom $showroom)
    {
        return $this->item($showroom->user, new UserTransformer());
    }
}
