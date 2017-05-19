<?php

namespace App\Http\Requests\Api\Notification;

use App\Http\Requests\Api\Request;

class ReadRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => 'required_unless:type,all,time|array',
        ];
    }
}
