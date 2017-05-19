<?php

namespace App\Http\Requests\Api\Service;

use App\Http\Requests\Api\Request;

class UpdateRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string',
            'category_tag' => 'required|in:SERIES,SINGLE',
            'category_id' => 'required_if:category_tag,SINGLE',
            'price' => 'numeric|min:0.01',
            'duration' => 'integer|min:0',
            'cover_picture_url' => 'string',
            'attachments' => 'array',
            'description' => 'string',
            'status' => 'in:ACTIVE,INACTIVE,active,inactive',
        ];
    }
}
