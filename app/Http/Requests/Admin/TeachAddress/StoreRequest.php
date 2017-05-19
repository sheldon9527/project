<?php

namespace App\Http\Requests\Admin\TeachAddress;

use App\Http\Requests\Admin\Request;

class StoreRequest extends Request
{
    public function rules()
    {
        return [
            'name'        => 'required|string|max:32',
            'category_id' => 'required|numeric',
            'telephone'   => 'required|string',
            'address'     => 'required|string|max:64',
        ];
    }

    public function messages()
    {
        return [
            'name.max'    => '最多输入32字节!',
            'address.max' => '最多输入64字节!',
        ];
    }
}
