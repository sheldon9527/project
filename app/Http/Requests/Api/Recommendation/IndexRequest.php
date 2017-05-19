<?php

namespace App\Http\Requests\Api\Recommendation;

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
            'type' => 'required|in:DESIGNER,MAKER,WORK,SERVICE,designer,maker,work,service',
        ];
    }
}