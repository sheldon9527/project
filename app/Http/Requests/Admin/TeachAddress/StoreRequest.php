<?php

namespace App\Http\Requests\Admin\TeachAddress;

use App\Http\Requests\Admin\Request;

class StoreRequest extends Request
{
    public function rules()
    {
        $rules = [
            'name'        => 'required|string|max:32',
            'category_id' => 'required|numeric',
            'address'     => 'required|string|max:64',
        ];
        $telephone = $this->request->get('telephone');
        if (!$telephone) {
            $rules['telephone'] = 'required';
        }
        if (count(explode('-', $telephone)) > 1) {
            $rules['telephone'] = 'required|regex:/^([0-9]{3,4}-)?[0-9]{7,8}$/';
        } else {
            $rules['telephone'] = 'required|regex:/^1[3-5,7,8]{1}[0-9]{9}$/';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.max'           => '最多输入32字节!',
            'address.max'        => '最多输入64字节!',
            'telephone.required' => '联系方式不能为空',
            'telephone.regex'    => '联系方式填写有误',
        ];
    }
}
