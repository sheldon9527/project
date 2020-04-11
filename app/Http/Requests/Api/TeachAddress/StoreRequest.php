<?php

namespace App\Http\Requests\Api\TeachAddress;

use App\Http\Requests\Api\Request;

class StoreRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules =  [
            'category_id' => 'integer|required',
            'name'        => 'string|required|max:32',
            'address'     => 'string|required|max:64',
            'telephone'   => 'integer|required',
            'latitude'    => 'string',
            'longitude'   => 'string',
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
            'category_id.integer' => '分类id必须为数字',
            'name.max'            => '最多输入32字节!',
            'address.max'         => '目的地地址不能超哥64字符!',
            'telephone.required'  => '联系方式不能为空',
            'telephone.regex'     => '联系方式填写有误',
        ];
    }
}
