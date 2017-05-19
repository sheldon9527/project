<?php

namespace App\Http\Requests\Admin\TeachAddress;

use App\Http\Requests\Admin\Request;

class UpdateRequest extends Request
{
    public function rules()
    {
        return [
            'category_id' => 'numeric',
            'address'     => 'max:64',
            'telephone'   => 'string',
        ];
    }

    public function messages()
    {
        return [
            'category_id.numeric' => '分类id必须为数字',
            'address.max'         => '目的地地址不能超哥64字符!',
        ];
    }
}
