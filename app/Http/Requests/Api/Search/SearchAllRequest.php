<?php

namespace App\Http\Requests\Api\Search;

use App\Http\Requests\Api\Request;

class SearchAllRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }
}
