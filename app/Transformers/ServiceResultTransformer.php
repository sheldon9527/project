<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\ServiceResult;

class ServiceResultTransformer extends TransformerAbstract
{
    public function transform(ServiceResult $result)
    {
        return $result->attributesToArray();
    }
}
