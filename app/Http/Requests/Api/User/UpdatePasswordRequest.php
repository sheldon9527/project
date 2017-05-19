<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\Request;

class UpdatePasswordRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'password' => 'required|alpha_num|between:6,20|confirmed',
            'password_confirmation' => 'required|alpha_num|between:6,20',
        ];

        if ($this->user()->password) {
            $rules['old_password'] = 'alpha_num|between:6,20';
        }

        return $rules;
    }
}
