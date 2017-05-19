<?php

namespace app\Http\Requests\Api\InquiryService;

use App\Http\Requests\Api\Request;

class UpdateRequest extends Request
{
    public function rules()
    {
        return [
            'category_ids'    => 'array',
            'min_price'       => 'numeric',
            'service_results' => 'array',
            'works'           => 'array',
        ];
    }
}