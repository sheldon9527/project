<?php

namespace App\Http\Requests\Api\SampleOrder;

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
            'inquiry_order_id' => 'required|integer|exists:inquiry_orders,id,owner_id,'.$this->user()->id,
        ];
    }
}
