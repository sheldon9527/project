<?php

namespace App\Transformers;

use App\Models\UserAddress;
use League\Fractal\TransformerAbstract;

class AddressTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'country',
        'province',
        'city',
    ];

    public function transform(UserAddress $address)
    {
        return $address->attributesToArray();
    }

    public function includeCountry(UserAddress $address)
    {
        if ($address->country) {
            return $this->item($address->country, new CountryTransformer());
        }
    }

    public function includeProvince(UserAddress $address)
    {
        if ($address->province) {
            return $this->item($address->province, new CountryTransformer());
        }
    }

    public function includeCity(UserAddress $address)
    {
        if ($address->city) {
            return $this->item($address->city, new CountryTransformer());
        }
    }
}
