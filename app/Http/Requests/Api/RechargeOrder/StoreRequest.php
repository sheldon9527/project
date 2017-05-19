<?php

namespace App\Http\Requests\Api\RechargeOrder;

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
        return [
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:PAYPAL,ALIPAY,paypal,alipay',
            'success_return_url' => 'required|string',
        ];
    }
}
