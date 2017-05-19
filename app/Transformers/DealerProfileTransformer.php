<?php

namespace App\Transformers;

use App\Models\DealerProfile;
use League\Fractal\TransformerAbstract;

class DealerProfileTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'country',
        'province',
        'city',
    ];

    public function transform(DealerProfile $profile)
    {
        if ($extraInfo = $profile->extra) {
            if (array_key_exists('want_do', $extraInfo)) {
                $profile->want_do = $extraInfo['want_do'];
            }

            if (array_key_exists('shop_urls', $extraInfo)) {
                $profile->shop_urls = $extraInfo['shop_urls'];
            }
        }

        $result = $profile->toArray();
        unset($result['extra']);

        return $result;
    }

    public function includeCountry(DealerProfile $profile)
    {
        if ($profile->country) {
            return $this->item($profile->country, new CountryTransformer());
        }
    }

    public function includeProvince(DealerProfile $profile)
    {
        if ($profile->province) {
            return $this->item($profile->province, new CountryTransformer());
        }
    }

    public function includeCity(DealerProfile $profile)
    {
        if ($profile->city) {
            return $this->item($profile->city, new CountryTransformer());
        }
    }
}
