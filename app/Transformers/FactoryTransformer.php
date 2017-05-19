<?php

namespace App\Transformers;

use App\Models\Factory;
use League\Fractal\TransformerAbstract;

class FactoryTransformer extends TransformerAbstract
{
    //手动加载
    protected $availableIncludes = [
        'contents',
        'country',
        'province',
        'city',
    ];

    public function transform(Factory $factory)
    {
        if ($factory->cover_picture_url) {
            $factory->cover_picture_url = $factory->getCloudUrl($factory->cover_picture_url);
        }

        if ($factory->contact_avatar) {
            $factory->contact_avatar = $factory->getCloudUrl($factory->contact_avatar);
        }

        if ($factory->extra) {
            foreach ($factory->extra as $extraKey => $extraValue) {
                $factory->$extraKey = $extraValue;
            }
        }

        $factory->empolyee_number = $factory->convertToSection('empolyee_number');
        $factory->mini_order_quantity = $factory->convertToSection('mini_order_quantity');
        $factory->sample_cycle = $factory->convertToSection('sample_cycle');

        $result = $factory->attributesToArray();

        return $result;
    }

    public function includeCountry(Factory $factory)
    {
        if ($factory->country) {
            return $this->item($factory->country, new CountryTransformer());
        }
    }

    public function includeProvince(Factory $factory)
    {
        if ($factory->province) {
            return $this->item($factory->province, new CountryTransformer());
        }
    }

    public function includeCity(Factory $factory)
    {
        if ($factory->city) {
            return $this->item($factory->city, new CountryTransformer());
        }
    }

    public function includeContents(Factory $factory)
    {
        if ($factory->contents) {
            return $this->collection($factory->contents, new FactoryContentTransformer());
        }
    }
}
