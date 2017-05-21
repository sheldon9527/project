<?php

namespace App\Http\Requests\Api\TeachAddress;

use App\Http\Requests\Api\Request;

class StoreRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id' => 'integer|required',
            'name'        => 'string|required',
            'address'     => 'string|required',
            'telephone'   => 'integer|required',
            'latitude'    => 'string',
            'longitude'   => 'string',
        ];
    }
}
