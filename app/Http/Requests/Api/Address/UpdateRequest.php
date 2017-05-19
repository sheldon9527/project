<?php

namespace App\Http\Requests\Api\Address;

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
            'contact' => 'string',
            'contact_cellphone' => 'string',
            'contact_email' => 'string',
            'address' => 'string|required',
            'country_region_id' => 'integer|required',
            'province_region_id' => 'integer',
            'city_region_id' => 'integer',
            'postcode' => 'string',
            'note' => 'string',
        ];
    }
}
