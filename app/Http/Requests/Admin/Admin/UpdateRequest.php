<?php

namespace App\Http\Requests\Admin\Admin;

use App\Http\Requests\Admin\Request;

class UpdateRequest extends Request
{
    public function rules()
    {
        return [
            'cellphone' => 'numeric',
            'email'     => 'email|string',
            'password'  => 'between:6,12',
            'username'  => 'required|string|alpha_num',
            'status'    => 'required|in:ACTIVE,INACTIVE',
            'roles'     => 'array'
        ];
    }

    public function messages()
    {
        return [
            'cellphone.numeric'  => '请填写正确格式的手机号!',
            'email.email'        => '请填写正确格式的邮箱!',
            'password.between'   => '请填写正确的密码范围!',
            'username.required'  => '请添加登陆账号',
            'username.string'    => '请正确填写登陆账号',
            'username.alpha_num' => '请正确填写登陆账号',
            'status.required'    => '请选择类型!',
            'status.in'          => '请选择正确的类型!',
        ];
    }
}