<?php

namespace App\Transformers;

use App\Models\UserProfile;
use League\Fractal\TransformerAbstract;

class ProfileTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'country',
        'province',
        'city',
    ];

    public function transform(UserProfile $profile)
    {
        $lang = \App::getLocale();

        if ($extraInfo = $profile->extra) {
            if (array_key_exists('en_careers', $extraInfo) && $lang != 'zh') {
                $profile->careers = $extraInfo['en_careers'];
            } elseif (array_key_exists('careers', $extraInfo)) {
                $profile->careers = $extraInfo['careers'];
            }
            if (array_key_exists('en_educations', $extraInfo) && $lang != 'zh') {
                $profile->educations = $extraInfo['en_educations'];
            } elseif (array_key_exists('educations', $extraInfo)) {
                $profile->educations = $extraInfo['educations'];
            }
        }

        $result = $profile->attributesToArray();

        return $result;
    }

    public function includeCountry(UserProfile $profile)
    {
        if ($profile->country) {
            return $this->item($profile->country, new CountryTransformer());
        }
    }

    public function includeProvince(UserProfile $profile)
    {
        if ($profile->province) {
            return $this->item($profile->province, new CountryTransformer());
        }
    }

    public function includeCity(UserProfile $profile)
    {
        if ($profile->city) {
            return $this->item($profile->city, new CountryTransformer());
        }
    }
}
