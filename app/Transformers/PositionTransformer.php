<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Position;

class PositionTransformer extends TransformerAbstract
{
    public function transform(Position $position)
    {
        return $position->attributesToArray();
    }
}
