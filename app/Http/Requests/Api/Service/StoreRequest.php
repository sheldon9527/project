<?php

namespace App\Http\Requests\Api\Service;

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
        return  $this->user()->type == 'DESIGNER' ? true : false;
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
            'category_tag' => 'required|in:SERIES,SINGLE',
            'category_id' => 'required_if:category_tag,SINGLE',
            'price' => 'numeric|min:0.01',
            'duration' => 'numeric',
            'attachments' => 'array',
            'cover_picture_url' => 'string',
            'description' => 'string',
            'custom_category' => 'string',
        ];
    }
}
