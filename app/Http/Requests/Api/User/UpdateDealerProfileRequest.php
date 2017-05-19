<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\Request;

class UpdateDealerProfileRequest extends Request
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
            'gender' => 'required|string|in:MALE,FEMALE,SECRET',
            'country_region_id' => 'integer|exists:regions,id',
            'province_region_id' => 'integer|exists:regions,id',
            'city_region_id' => 'integer|exists:regions,id',
            'avatar_url' => 'required_without:avatar|url',
            'avatar' => 'required_without:avatar_url',
            'avatar_cut' => 'array',
            'category_ids' => 'array|max:10',
            'qq' => 'required_without_all:weixin,email,cellphone|string',
            'weixin' => 'required_without_all:qq,email,cellphone|string',
            'email' => 'required_without_all:weixin,qq,cellphone|email',
            'cellphone' => 'required_without_all:weixin,email,qq|string',
            'want_do' => 'required|array',
            'shop_urls' => 'array|max:5',
        ];
    }
}
