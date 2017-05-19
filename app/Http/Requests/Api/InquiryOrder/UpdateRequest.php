<?php

namespace App\Http\Requests\Api\InquiryOrder;

use App\Http\Requests\Api\Request;

class UpdateRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'operate' => 'required|in:finish,cancel',
        ];

        return $rules;
    }
}
