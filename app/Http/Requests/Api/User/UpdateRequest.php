<?php

namespace App\Http\Requests\Api\User;

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
            'avatar_url' => 'required_without:avatar|url',
            'avatar' => 'required_without:avatar_url',
        ];

        if ($this->user()->type != 'MAKER') {
            $rules += [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'gender' => 'required|string|in:MALE,FEMALE,SECRET',
            ];
        }

        return $rules;
    }
}
