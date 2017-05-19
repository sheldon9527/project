<?php

namespace App\Transformers;

class RecommendMakerTransformer extends MakerTransformer
{
    protected $defaultIncludes = [
        'categories',
        'factory',
    ];
}
