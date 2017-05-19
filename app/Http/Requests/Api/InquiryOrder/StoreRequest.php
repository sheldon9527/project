<?php

namespace App\Http\Requests\Api\InquiryOrder;

use App\Http\Requests\Api\Request;

class StoreRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return  $this->user()->type == 'DESIGNER' ? false : true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'style_no' => 'required|string',
            'category_id' => 'required|integer|exists:categories,id',
            'production_no' => 'required|string',
            'address_id' => 'required|integer|exists:user_addresses,id,user_id,'.$this->user()->id,
            'attachments' => 'required|array',
            'product_weight' => 'numeric|min:0',
            'production_no' => 'numeric|min:0',
            'cover_picture_url' => 'string',
            'note' => 'string',
            'contact_id' => 'integer',
            'publish' => 'boolean',
        ];
    }
}
