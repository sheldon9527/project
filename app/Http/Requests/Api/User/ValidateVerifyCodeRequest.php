<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\Request;

class ValidateVerifyCodeRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string',
            'verify_code' => 'required',
        ];
    }
}
