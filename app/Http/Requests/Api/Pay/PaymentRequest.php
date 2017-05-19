<?php

namespace App\Http\Requests\Api\Pay;

use App\Http\Requests\Api\Request;

class PaymentRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_no' => 'required|string',
            'pay_mode' => 'required|string|in:defara,alipay,paypal',
            'success_return_url' => 'required_if:pay_mode,alipay,paypal|string',
            'password' => 'required_if:pay_mode,defara',
        ];
    }
}
