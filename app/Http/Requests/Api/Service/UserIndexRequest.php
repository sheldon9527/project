<?php

namespace App\Http\Requests\Api\Service;

use App\Http\Requests\Api\Request;

class UserIndexRequest extends Request
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
            'page' => 'integer',
            'category_ids' => 'string',
            'min_price' => 'integer',
            'max_price' => 'integer',
        ];
    }
}
