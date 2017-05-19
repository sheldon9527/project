<?php

namespace App\Http\Requests\Api\Authentication;

use App\Http\Requests\Api\Request;

class MakerStoreRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'factory_name' => 'required|string',
            'contact' => 'required|string',
            'contact_cellphone' => 'required|numeric',
            'email' => 'string',
            'location.detail' => 'string',
            'location.country_region_id' => 'integer',
            'location.province_region_id' => 'integer',
            'location.city_region_id' => 'integer',
        ];
    }
}
