<?php

namespace App\Http\Requests\Api\ProductionOrder;

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
            'purchase_order_id' => 'required|integer|exists:purchase_orders,id,owner_id,'.$this->user()->id,
        ];
    }
}
