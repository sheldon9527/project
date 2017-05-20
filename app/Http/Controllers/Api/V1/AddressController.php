<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\AddressTransformer;
use App\Http\Requests\Api\Address\StoreRequest;
use App\Http\Requests\Api\Address\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserAddress;

class AddressController extends BaseController
{
    /**
     * @apiGroup Addresses
     * @apiDescription  地址列表
     *
     * @api {get} /user/addresses 地址列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": [
     *      {
     *          "id": 1,
     *          "user_id": 20,
     *          "contact": "黄典",
     *          "contact_cellphone": "18502880126",
     *          "contact_email": null,
     *          "address": "高新区吉泰路666号",
     *          "country_ISO": "CN",
     *          "postcode": null,
     *          "is_default": 0,
     *          "note": null,
     *          "country_region_id": 538,
     *          "province_region_id": 926,
     *          "city_region_id": 929,
     *          "created_at": "2016-03-10 10:54:20",
     *          "updated_at": "2016-03-10 11:09:49",
     *          "country": {
     *              "id": 538,
     *              "name": "中国",
     *              "english_name": "China",
     *              "iso3": "CHN",
     *              "iso2": "CN",
     *          },
     *          "province": {
     *              "id": 926,
     *              "name": "四川",
     *              "english_name": "Sichuan",
     *              "iso3": "51",
     *              "iso2": null,
     *          },
     *          "city": {
     *              "id": 929,
     *              "name": "成都",
     *              "english_name": "Chengdu",
     *              "iso3": "1",
     *              "iso2": null,
     *          }
     *        }
     *      ],
     *  "meta": {
     *      "pagination": {
     *          "total": 5,
     *          "count": 5,
     *          "per_page": 20,
     *          "current_page": 1,
     *          "total_pages": 1,
     *          "links": []
     *      }
     *  }
     * }
     */
    public function index(Request $request)
    {
        $user = \Auth::User();
        $addresses = $user->addresses()->get();

        $defaultAddress = $user->addresses()->where('is_default', 1)->lists('id')->first();

        return $this->response()->collection($addresses, new AddressTransformer())->setMeta(['default_address_id' => $defaultAddress]);
    }

    /**
     * @apiGroup Addresses
     * @apiDescription 添加地址
     *
     * @api {post} /addresses 添加地址
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Integer} id 用户地址id
     * @apiParam {String} contact 联系人
     * @apiParam {String} contact_cellphone 联系人电话
     * @apiParam {String} [contact_email] 联系人邮箱
     * @apiParam {Integer} country_region_id 国家
     * @apiParam {Integer} province_region_id 省/州
     * @apiParam {Integer} city_region_id 市/镇
     * @apiParam {String} address 详细地址
     * @apiParam {String} postcode 邮政编码
     * @apiParam {String} [note] 备注
     * @apiParam {Integer} [is_default] false:(不是默认)/true:(是默认),不传默认为:false
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 created
     *     /**
     * @apiGroup Addresses
     * @apiDescription 地址详情
     *
     * @api {get} /addresses/{id} 地址详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Integer} id 用户地址id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": {
     *      "id": 2,
     *      "user_id": 20,
     *      "contact": "黄典",
     *      "contact_cellphone": "18502880126",
     *      "contact_email": null,
     *      "address": "高新区吉泰路666号",
     *      "country_ISO": "CN",
     *      "postcode": null,
     *      "is_default": 0,
     *      "note": "家",
     *      "created_at": "2016-03-10 10:54:20",
     *      "updated_at": "2016-03-10 10:54:20",
     *      "full_address": "foobar"
     *   }
     * }
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::User();
        $address = new UserAddress();
        $address->fill($request->all());
        //设置默认地址
        $isDefault = $request->get('is_default') == 'true' ? 1 : 0;
        $province = $request->get('province_region_id') ?: 0;
        $city = $request->get('city_region_id') ?: 0;
        $address->province_region_id = $province;
        $address->city_region_id = $city;
        if ($isDefault) {
            $user->addresses()->where('is_default', 1)->update(['is_default' => 0]);
            $address->is_default = 1;
        }
        $address->user()->associate($user)->save();

        return $this->response()->item($address, new AddressTransformer());
    }

    /**
     * @apiGroup Addresses
     * @apiDescription 地址详情
     *
     * @api {get} /addresses/{id} 地址详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Integer} id 用户地址id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": {
     *      "id": 2,
     *      "user_id": 20,
     *      "contact": "黄典",
     *      "contact_cellphone": "18502880126",
     *      "contact_email": null,
     *      "address": "高新区吉泰路666号",
     *      "country_ISO": "CN",
     *      "postcode": null,
     *      "is_default": 0,
     *      "note": "家",
     *      "created_at": "2016-03-10 10:54:20",
     *      "updated_at": "2016-03-10 10:54:20",
     *      "country": {
     *          "id": 538,
     *          "name": "中国",
     *          "en_name": "China",
     *          "iso3": "CHN",
     *          "iso2": "CN"
     *      },
     *      "province": {
     *          "id": 926,
     *          "name": "四川",
     *          "en_name": "Sichuan",
     *          "iso3": "51",
     *          "iso2": null
     *      },
     *      "city": {
     *          "id": 930,
     *          "name": "达州",
     *          "en_name": "Dazhou",
     *          "iso3": "17",
     *          "iso2": null
     *      }
     *   }
     * }
     */
    public function show($id, Request $request)
    {
        $user = \Auth::User();
        $address = $user->addresses()->find($id);
        if (!$address) {
            return $this->response->errorNotFound();
        }

        return $this->response()->item($address, new AddressTransformer());
    }

    /**
     * @apiGroup Addresses
     * @apiDescription 更新地址
     *
     * @api {put} /addresses/{id} 更新地址
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Integer} id 用户地址id
     * @apiParam {String} [contact] 联系人
     * @apiParam {String} [contact_cellphone] 联系人电话
     * @apiParam {String} [contact_email] 联系人邮箱
     * @apiParam {Integer} [country_region_id] 国家
     * @apiParam {Integer} [province_region_id] 省/州
     * @apiParam {Integer} [city_region_id] 市/镇
     * @apiParam {String}  address 详细地址
     * @apiParam {String} [postcode] 邮政编码
     * @apiParam {String} [note] 备注
     * @apiParam {Integer} is_default false:(不是默认)/true:(是默认),不传默认为:false
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::User();
        $address = $user->addresses()->find($id);

        if (!$address) {
            return $this->response->errorNotFound();
        }

        $address->fill($request->all());
        //设置默认地址
        $isDefault = $request->get('is_default') == 'true' ? 1 : 0;
        $province = $request->get('province_region_id') ?: 0;
        $city = $request->get('city_region_id') ?: 0;
        $address->province_region_id = $province;
        $address->city_region_id = $city;

        if (!$isDefault) {
            $address->is_default = 0;
        }

        if ($isDefault) {
            $user->addresses()->where('is_default', 1)->update(['is_default' => 0]);
            $address->is_default = 1;
        }

        $address->user()->associate($user);
        $address->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup Addresses
     * @apiDescription 删除地址
     *
     * @api {delete} /addresses/{id} 删除地址
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Integer} id 用户地址id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function destroy($id)
    {
        $user = \Auth::User();
        $address = $user->addresses()->find($id);
        if (!$address) {
            return $this->response->errorNotFound();
        }
        $result = $address->delete();
        if (!$result) {
            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }
}
