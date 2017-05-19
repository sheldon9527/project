<?php

namespace App\Http\Requests\Api\Work;

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
            'title' => 'required|string',
            'description' => 'required|string',
            'cover_picture_url' => 'required|url',
            'attachments' => 'array',
        ];
    }
}
