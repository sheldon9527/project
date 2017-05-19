<?php

namespace app\Http\Requests\Api\InquiryService;

use App\Http\Requests\Api\Request;

class StoreRequest extends Request
{
    public function rules()
    {
        return [
            'category_ids'    => 'required|array',
            'min_price'       => 'required|numeric',
            'service_results' => 'required|array',
            'works'           => 'required|array',
        ];
    }
}