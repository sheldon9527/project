<?php

namespace App\Http\Requests\Api\Address;

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
            'contact' => 'string|required',
            'contact_cellphone' => 'string|required',
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
