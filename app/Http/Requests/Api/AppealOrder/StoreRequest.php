<?php

namespace App\Http\Requests\Api\AppealOrder;

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
            'production_order_id' => 'required|integer|exists:production_orders,id,owner_id,'.$this->user()->id,
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'attachments' => 'array',
        ];
    }
}
