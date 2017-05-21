<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\TeachAddress\StoreRequest;
use App\Models\TeachAddress;
use App\Transformers\TeachAddressTransformer;
use Geohash\Geohash;
use Illuminate\Http\Request;

class TeachAddressController extends BaseController
{
    /**
     * @apiGroup teachAddresses
     * @apiDescription  目的地址列表
     *
     * @api {get} /teach/addresses 目的地址列表
     * @apiVersion 0.1.0
     * @apiDescription [例子:域名/teach/addresses?include=tags,attachments,category需要什么信息就include什么参数]
     * @apiParam {String} [include]  可引入的关系
     * @apiParam {String} [include.tags]    标签信息
     * @apiParam {String} [include.attachments] 图片信息
     * @apiParam {String} [include.category] 分类
     * @apiParam {string} [name] 地址名称
     * @apiParam {string} [longitude] 经度
     * @apiParam {string} [latitude] 纬度
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 35,
     *      "category_id": 81,
     *      "name": "博物馆",
     *      "address": "四川历史博物馆",
     *      "telephone": "010-234234234",
     *      "latitude": "30.551899",
     *      "longitude": "104.032122",
     *      "status": "ACTIVE",
     *      "description": "目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍",
     *      "special": "研学特色研学特色研学特色研学特色研学特色研学特色研学特色研学特色",
     *      "created_at": "2017-05-19 11:03:47",
     *      "updated_at": "2017-05-20 15:17:43",
     *      "cover_picture": "http://teach.dev/assets/teachAddress/17/05/618175033591ed137194ee.jpg",
     *      "category": {
     *        "data": {
     *          "id": 81,
     *          "name": "女鞋"
     *        }
     *      },
     *      "tags": {
     *        "data": [
     *          {
     *            "id": 68,
     *            "name": "研学",
     *            "teach_address_id": 35
     *          },
     *          {
     *            "id": 69,
     *            "name": "特色",
     *            "teach_address_id": 35
     *          }
     *        ]
     *      },
     *      "attachments": {
     *        "data": [
     *          {
     *            "id": 250,
     *            "relative_path": "http://teach.dev/assets/teachAddress/17/05/618175033591ed137194ee.jpg",
     *            "filename": "读书人 第一季.jpg",
     *            "mime_types": "image/jpeg"
     *          },
     *          {
     *            "id": 251,
     *            "relative_path": "http://teach.dev/assets/teachAddress/17/05/415383232591ed137194ed.jpg",
     *            "filename": "点五步.jpg",
     *            "mime_types": "image/jpeg"
     *          },
     *          {
     *            "id": 252,
     *            "relative_path": "http://teach.dev/assets/teachAddress/17/05/96969780591ed137288c2.jpg",
     *            "filename": "故事中国 2017.jpg",
     *            "mime_types": "image/jpeg"
     *          },
     *          {
     *            "id": 253,
     *            "relative_path": "http://teach.dev/assets/teachAddress/17/05/908829895591ed1372a790.jpg",
     *            "filename": "军武MINI.jpg",
     *            "mime_types": "image/jpeg"
     *          },
     *          {
     *            "id": 254,
     *            "relative_path": "http://teach.dev/assets/teachAddress/17/05/1030383544591ed1373708b.jpg",
     *            "filename": "趣猫猫的魔法之旅.jpg",
     *            "mime_types": "image/jpeg"
     *          },
     *          {
     *            "id": 255,
     *            "relative_path": "http://teach.dev/assets/teachAddress/17/05/370396620591ed13737b88.jpg",
     *            "filename": "十分游料.jpg",
     *            "mime_types": "image/jpeg"
     *          },
     *          {
     *            "id": 256,
     *            "relative_path": "http://teach.dev/assets/teachAddress/17/05/1451224129591ed137465f4.jpg",
     *            "filename": "铁血将军.jpg",
     *            "mime_types": "image/jpeg"
     *          },
     *          {
     *            "id": 257,
     *            "relative_path": "http://teach.dev/assets/teachAddress/17/05/1794763350591ed13746935.jpg",
     *            "filename": "徐行 2017.jpg",
     *            "mime_types": "image/jpeg"
     *          }
     *        ]
     *      }
     *    },
     *    {
     *      "id": 2,
     *      "category_id": 1,
     *      "name": "北海公园1",
     *      "address": "北京市西城区文津街1号",
     *      "telephone": "010-2343443",
     *      "latitude": "34.236080797698",
     *      "longitude": "109.0145193757",
     *      "status": "ACTIVE",
     *      "description": "积分卡拉到就疯狂拉升的积分卡时间段方可垃圾地方考拉多少级付款啦圣诞节快乐积分萨德里克109.0145193757109.0145193757积分卡拉到就疯狂拉升的积分卡时间段方可垃圾地方考拉多少级付款啦圣诞节快乐积分萨德里克109.0145193757109.0145193757积分卡拉到就疯狂拉升的积分卡时间段方可垃圾地方考拉多少级付款啦圣诞节快乐积分萨德里克109.0145193757109.0145193757",
     *      "special": "积分卡拉到就疯狂拉升的积分卡时间段方可垃圾地方考拉多少级付款啦圣诞节快乐积分萨德里克109.0145193757109.0145193757",
     *      "created_at": "2015-09-29 09:09:15",
     *      "updated_at": "2017-05-20 14:01:23",
     *      "cover_picture": "http://teach.dev/assets/1.jpg",
     *      "category": {
     *        "data": {
     *          "id": 1,
     *          "name": "女装"
     *        }
     *      },
     *      "tags": {
     *        "data": []
     *      },
     *      "attachments": {
     *        "data": [
     *          {
     *            "id": 231,
     *            "relative_path": "http://teach.dev/assets/1.jpg",
     *            "filename": "yixiaodong.jpg",
     *            "mime_types": "image/jpeg"
     *          }
     *        ]
     *      }
     *    }
     *  ]
     *}
     */
    public function index(Request $request)
    {
        $addresses = TeachAddress::query();
        if ($name = $request->get('name')) {
            $addresses = $addresses->where('name', 'like', '%' . $name . '%');
        }
        $longitude = $request->get('longitude');
        $latitude  = $request->get('latitude');

        $addresses = $addresses->where('status', 'ACTIVE')->orderBy('id', 'desc')->get();
        if ($longitude && $latitude) {
            foreach ($addresses as $key => $address) {
                $distance = TeachAddress::getDistance($latitude, $longitude, $address->latitude, $address->longitude);
                if (floor($distance / 5000) > 5) {
                    unset($addresses[$key]);
                }
            }
        }

        return $this->response()->collection($addresses, new TeachAddressTransformer());
    }

    /**
     * @apiGroup teachAddresses
     * @apiDescription 添加目的地地址
     *
     * @api {post} /teach/addresses 添加目的地地址
     * @apiVersion 0.1.0
     * @apiParam {Integer} category_id 目的地地址分类id
     * @apiParam {String} name 目的地名称
     * @apiParam {String} address 目的地地址
     * @apiParam {String} telephone 目的地联系方式
     * @apiParam {String} latitude 目的地纬度
     * @apiParam {String} longitude 目的地经度
     * @apiParam {String} [description] 目的地描述
     * @apiParam {String} [special] 目的地特色
     * @apiParam {Array} [tags] 目的地标签
     * @apiParam {Array} [attachments] 目的地图片
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 created
     */
    public function store(StoreRequest $request)
    {
        $address = new TeachAddress();
        $address->fill($request->all());
        $address->status  = 'NO_APPROVAL';
        $address->type    = 'OUT';
        $address->geohash = Geohash::encode($request->get('latitude'), $request->get('longitude'));
        $address->save();
        //attachments
        $attachments = $request->get('attachments');
        if ($attachments) {
            $address->updateAttachment($attachments, 'detail');
        }
        //tags
        if ($tags = $request->get('tags')) {
            foreach ($tags as $key => $tag) {
                $newTag                   = new Tags();
                $newTag->teach_address_id = $address->id;
                $newTag->name             = $tag;
                $newTag->save();
            }
        }

        return $this->response()->item($address, new TeachAddressTransformer());
    }

    /**
     * @apiGroup teachAddresses
     * @apiDescription 目的地地址详情
     *
     * @api {get} /teach/addresses/{id} 目的地地址详情
     * @apiVersion 0.1.0
     * @apiDescription [例子:域名/teach/addresses/{id}?include=tags,attachments,category需要什么信息就include什么参数]
     * @apiParam {String} [include]  可引入的关系
     * @apiParam {String} [include.tags]    标签信息
     * @apiParam {String} [include.attachments] 图片信息
     * @apiParam {String} [include.category] 分类
     * @apiParam {Integer} id 目的地地址id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": {
     *    "id": 35,
     *    "category_id": 81,
     *    "name": "博物馆",
     *    "address": "四川历史博物馆",
     *    "telephone": "010-234234234",
     *    "latitude": "30.551899",
     *    "longitude": "104.032122",
     *    "status": "ACTIVE",
     *    "description": "目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍目的地介绍",
     *    "special": "研学特色研学特色研学特色研学特色研学特色研学特色研学特色研学特色",
     *    "created_at": "2017-05-19 11:03:47",
     *    "updated_at": "2017-05-20 15:17:43",
     *    "cover_picture": "http://teach.dev/assets/teachAddress/17/05/618175033591ed137194ee.jpg",
     *    "category": {
     *      "data": {
     *        "id": 81,
     *        "name": "女鞋"
     *      }
     *    },
     *    "tags": {
     *      "data": [
     *        {
     *          "id": 68,
     *          "name": "研学",
     *          "teach_address_id": 35
     *        },
     *        {
     *          "id": 69,
     *          "name": "特色",
     *          "teach_address_id": 35
     *        }
     *      ]
     *    },
     *    "attachments": {
     *      "data": [
     *        {
     *          "id": 250,
     *          "relative_path": "http://teach.dev/assets/teachAddress/17/05/618175033591ed137194ee.jpg",
     *          "filename": "读书人 第一季.jpg",
     *          "mime_types": "image/jpeg"
     *        },
     *        {
     *          "id": 251,
     *          "relative_path": "http://teach.dev/assets/teachAddress/17/05/415383232591ed137194ed.jpg",
     *          "filename": "点五步.jpg",
     *          "mime_types": "image/jpeg"
     *        },
     *        {
     *          "id": 252,
     *          "relative_path": "http://teach.dev/assets/teachAddress/17/05/96969780591ed137288c2.jpg",
     *          "filename": "故事中国 2017.jpg",
     *          "mime_types": "image/jpeg"
     *        },
     *        {
     *          "id": 253,
     *          "relative_path": "http://teach.dev/assets/teachAddress/17/05/908829895591ed1372a790.jpg",
     *          "filename": "军武MINI.jpg",
     *          "mime_types": "image/jpeg"
     *        },
     *        {
     *          "id": 254,
     *          "relative_path": "http://teach.dev/assets/teachAddress/17/05/1030383544591ed1373708b.jpg",
     *          "filename": "趣猫猫的魔法之旅.jpg",
     *          "mime_types": "image/jpeg"
     *        },
     *        {
     *          "id": 255,
     *          "relative_path": "http://teach.dev/assets/teachAddress/17/05/370396620591ed13737b88.jpg",
     *          "filename": "十分游料.jpg",
     *          "mime_types": "image/jpeg"
     *        },
     *        {
     *          "id": 256,
     *          "relative_path": "http://teach.dev/assets/teachAddress/17/05/1451224129591ed137465f4.jpg",
     *          "filename": "铁血将军.jpg",
     *          "mime_types": "image/jpeg"
     *        },
     *        {
     *          "id": 257,
     *          "relative_path": "http://teach.dev/assets/teachAddress/17/05/1794763350591ed13746935.jpg",
     *          "filename": "徐行 2017.jpg",
     *          "mime_types": "image/jpeg"
     *        }
     *      ]
     *    }
     *  }
     *}
     */
    public function show($id, Request $request)
    {
        $address = TeachAddress::find($id);
        if (!$address) {
            return $this->response->errorNotFound();
        }

        return $this->response()->item($address, new TeachAddressTransformer());
    }
}
