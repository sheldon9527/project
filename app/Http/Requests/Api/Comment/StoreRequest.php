<?php

namespace App\Http\Requests\Api\Comment;

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
            'order_id' => 'required|integer',
            'order_type' => 'required|in:inquiry_service,inquiry,sample,production',
            'content' => 'required',
        ];
    }
}
