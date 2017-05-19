<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Style;

class StyleTransformer extends TransformerAbstract
{
    public function transform(Style $style)
    {
        return $style->attributesToArray();
    }
}
