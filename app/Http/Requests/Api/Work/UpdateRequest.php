<?php

namespace App\Http\Requests\Api\Work;

use App\Http\Requests\Api\Request;

class UpdateRequest extends Request
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
            'title' => 'string',
            'description' => 'string',
            'cover_picture_url' => 'string',
            'orderby' => 'string|in:UP,DOWN',
            'status' => 'string|in:ACTIVE,INACTIVE,active,inactive',
        ];
    }
}
