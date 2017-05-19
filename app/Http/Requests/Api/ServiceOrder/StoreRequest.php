<?php

namespace App\Http\Requests\Api\ServiceOrder;

use App\Http\Requests\Api\Request;

class StoreRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'service_id' => 'required|integer',
            'service_type' => 'required|in:spring_summer,fall_winter',
            'description' => 'string',
            'attachments' => 'array',
        ];
    }
}
