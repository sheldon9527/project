<?php

namespace App\Http\Requests\Api\PurchaseOrder;

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
            'sample_order_id' => 'required|integer|exists:sample_orders,id,owner_id,'.$this->user()->id,
            'address_id' => 'integer|exists:user_addresses,id,user_id,'.$this->user()->id,
            'production_duration' => 'required|integer',
            'production_standards' => 'required|array',
            'production_price' => 'required|numeric',
            'transport_method' => 'required_if:is_normal_transport,0',
            'is_normal_transport' => 'required|boolean',
            'size_table' => 'array', // attachment 数组
            'auxiliary_datas' => 'array', //attachment 数组
        ];
    }
}
