<?php

namespace App\Http\Requests\Api\Contract;

use App\Http\Requests\Api\Request;

class ShowRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_no' => 'required',
        ];
    }
}
