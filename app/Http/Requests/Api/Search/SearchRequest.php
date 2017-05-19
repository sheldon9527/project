<?php

namespace App\Http\Requests\Api\Search;

use App\Http\Requests\Api\Request;

class SearchRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|in:service,designer,maker',
            'name' => 'required',
        ];
    }
}
