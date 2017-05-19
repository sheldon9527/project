<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\Request;

class VerifyCodeRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        $username = $this->request->get('username');

        if (!$username) {
            $rules['username'] = 'required';
        } elseif (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            //email
            $rules['username'] = 'required|email|unique:users,email';
        } else {
            //phone
            $rules['username'] = 'required|unique:users,cellphone|regex:/^1[3-5,7,8]{1}[0-9]{9}$/';
        }

        return $rules;
    }
}
