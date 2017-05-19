<?php

namespace App\Http\Requests\Api\WithdrawOrder;

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
        $user = $this->user();

        $existAccountName = $user->account_name;

        return [
            'type' => 'required|in:BANKCARD,PAYPAL,ALIPAY,alipay,paypal,bankcard',
            'amount' => 'required|numeric|min:0.01',
            'bank_name' => 'required_if:type,BANKCARD',
            'account_name' => $existAccountName ? '' : 'required_if:type,BANKCARD',
            'account_no' => 'required|string',
        ];
    }
}
