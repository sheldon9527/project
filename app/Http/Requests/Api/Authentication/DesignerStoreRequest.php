<?php

namespace App\Http\Requests\Api\Authentication;

use App\Http\Requests\Api\Request;

class DesignerStoreRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'avatar' => 'required|string',
            'gender' => 'required|string',
            'position_id' => 'required|integer',
            'location.country_region_id' => 'required|integer',
            'description' => 'required|string',
            'educations' => 'required|array',
            'careers' => 'required|array',
            'brandChoose' => 'required|integer',
            'brands' => 'required_if:brandChoose,1|array|min:1|max:5',
            'styles' => 'required|array|min:1|max:5',
            'services' => 'required|array|min:1'
        ];
    }
}
