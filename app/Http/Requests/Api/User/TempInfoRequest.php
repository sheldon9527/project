<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\Request;

class TempInfoRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'contact_info' => 'required|string',
        ];
    }
}
