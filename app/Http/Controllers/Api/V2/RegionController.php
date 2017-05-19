<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Transformers\CountryTransformer;
use App\Transformers\RegionTransformer;

class RegionController extends BaseController
{
    /**
     * @apiGroup others
     * @apiDescription 地区列表
     *
     * @api {get} /regions 地区列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiSuccessExample {json} Success-Response  全部地区及国家:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "阿富汗",
     *       "iso3": "AFG",
     *       "iso2": "AF",
     *       "children": {
     *         "data": [
     *           {
     *             "id": 2,
     *             "name": "赫拉特",
     *             "iso3": "HEA",
     *             "iso2": null
     *           },
     *           {
     *             "id": 3,
     *             "name": "喀布尔",
     *             "iso3": "KBL",
     *             "iso2": null
     *           },
     *           {
     *             "id": 4,
     *             "name": "坎大哈",
     *             "iso3": "KDH",
     *             "iso2": null
     *           },
     *           {
     *             "id": 5,
     *             "name": "马扎里沙里夫",
     *             "iso3": "MZR",
     *             "iso2": null
     *           }
     *         ]
     *       }
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $regions = Region::where('country_id', 0)->get();

        return $this->response->collection($regions, new RegionTransformer());
    }

    /**
     * @apiGroup others
     * @apiDescription 国家列表
     *
     * @api {get} /countries 国家列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "阿富汗",
     *       "iso3": "AFG",
     *       "iso2": "AF"
     *     },
     *     {
     *       "id": 6,
     *       "name": "奥兰群岛",
     *       "iso3": "ALA",
     *       "iso2": "AX"
     *     },
     *   ]
     * }
     */
    public function countryIndex()
    {
        $countries = Region::where('country_id', 0)->get();

        return $this->response->collection($countries, new CountryTransformer());
    }

    /**
     * @apiGroup others
     * @apiDescription 热门国家列表
     *
     * @api {get} /hot/countries 热门国家列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [number]  多少热门国家
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 1608,
     *      "name": "意大利",
     *      "iso2": "IT",
     *      "country_count": 16
     *    },
     *    {
     *      "id": 3678,
     *      "name": "美国",
     *      "iso2": "US",
     *      "country_count": 11
     *    },
     *    {
     *      "id": 3610,
     *      "name": "英国",
     *      "iso2": "GB",
     *      "country_count": 7
     *    },
     *    {
     *      "id": 2761,
     *      "name": "葡萄牙",
     *      "iso2": "PT",
     *      "country_count": 3
     *    },
     *    {
     *      "id": 1308,
     *      "name": "法国",
     *      "iso2": "FR",
     *      "country_count": 3
     *    }
     *  ]
     *}
     */
    public function hotCountryIndex(Request $request)
    {
        //热门国家
        $validator = \Validator::make($request->all(), [
            'number' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages()->all());
        }

        $number = $request->get('number') ?: 5;

        if (\App::getLocale() == 'zh') {
            $filter = [
                'regions.id', 'regions.name', 'regions.iso2', \DB::raw('count(*) as country_count'),
            ];
        } else {
            $filter = [
                'regions.id', 'regions.en_name as name', 'regions.iso2', \DB::raw('count(*) as country_count'),
            ];
        }

        $countryHots = \DB::table('regions')
            ->leftJoin('designer_profiles', 'regions.id', '=', 'designer_profiles.country_region_id')
            ->leftJoin('users', 'users.id', '=', 'designer_profiles.user_id')
            ->where('users.status', '=', 'ACTIVE')
            ->where('users.type', 'DESIGNER')
            ->select($filter)
            ->groupBy('regions.id')
            ->orderBy('country_count', 'desc')
            ->take($number)
            ->get();

        foreach ($countryHots as $countryHot) {
            $country['data'][] = $countryHot;
        }

        return $country;
    }

    /**
     * @apiGroup others
     * @apiDescription json国家列表
     *
     * @api {get} /json/countries json国家列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     */
    public function JsonCountryIndex()
    {
        if (app()->getLocale() == 'zh') {
            $filter = ['id', 'name', 'country_id', 'iso2'];
        } else {
            $filter = ['id', 'en_name', 'country_id', 'iso2'];
        }

        $regions = Region::select($filter);

        $regions = $regions->get();

        $result = $this->getCountry($regions, 0);

        return $this->response->array($result);
    }

    /**
     * @param $data
     * @param $pid
     *
     * @return array|string
     */
    public function getCountry($regions, $pid)
    {
        $tree = [];
        foreach ($regions as $region) {
            if ($region->country_id == $pid) {
                $childs = $this->getCountry($regions, $region->id);
                if ($childs) {
                    $region['data'] = $childs;
                }
                $tree[] = $region;
            }
        }

        return $tree;
    }

    public function formatRegion()
    {
        if (app()->getLocale() == 'zh') {
            $filter = ['id', 'name', 'country_id', 'iso2'];
        } else {
            $filter = ['id', 'en_name', 'country_id', 'iso2'];
        }

        $regions = Region::select($filter);
        $regions = $regions->where('country_id', 0)->get();

        foreach ($regions as $region) {
            $childs = Region::select($filter)->where('country_id', $region->id)->get();
            if ($childs->count()) {
                $region['data'] = $childs;
                foreach ($childs as $child) {
                    $lastChilds = Region::select($filter)->where('country_id', $child->id)->get();
                    if ($lastChilds->count()) {
                        $child['data'] = $lastChilds;
                    }
                }
            }
            $data[] = $region;
        }

        return $data;
    }
}
