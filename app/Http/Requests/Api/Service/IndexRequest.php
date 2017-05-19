<?php

namespace App\Http\Requests\Api\Service;

use App\Http\Requests\Api\Request;

class IndexRequest extends Request
{
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
            'name' => 'string',
            'min_price' => 'integer',
            'max_price' => 'integer',
        ];
    }
}
