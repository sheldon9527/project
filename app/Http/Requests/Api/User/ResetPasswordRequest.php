<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\Request;

class ResetPasswordRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'verify_token' => 'required',
            'password' => 'required|alpha_num|between:6,20|confirmed',
            'password_confirmation' => 'required|alpha_num|between:6,20',
        ];
    }
}
