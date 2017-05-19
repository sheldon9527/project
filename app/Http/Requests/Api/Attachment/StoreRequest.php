<?php

namespace App\Http\Requests\Api\Attachment;

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
            'is_async' => 'boolean',
            'attachment' => 'required',
            'type' => 'required|in:sample_order_records,service_order,inquiry_order,sample_order,purchase_order,production_order,work,service,factory,appeal_orders,appeal_orders_intervene',
            'tag' => 'string|in:detail,cover,first,final,owner,contact,invoice,pack_order',
        ];
    }
}
