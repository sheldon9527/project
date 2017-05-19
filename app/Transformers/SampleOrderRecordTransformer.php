<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\SampleOrderRecord;

class SampleOrderRecordTransformer extends TransformerAbstract
{
    public function transform(SampleOrderRecord $record)
    {
        return $record->attributesToArray();
    }
}
