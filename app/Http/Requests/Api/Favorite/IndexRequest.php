<?php

namespace App\Http\Requests\Api\Favorite;

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
            'type' => 'in:designer,product,maker,service,work',
        ];
    }
}
