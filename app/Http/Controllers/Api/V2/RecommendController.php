<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Recommendation\IndexRequest;
use App\Transformers\RecommendTransformer;
use App\Transformers\MakerTransformer;
use App\Transformers\DesignerTransformer;
use App\Transformers\WorkTransformer;
use App\Transformers\ServiceTransformer;
use App\Models\Recommendation;
use App\Models\DesignerWork;
use App\Models\Service;
use App\Models\User;

class RecommendController extends BaseController
{
    /**
     * @apiGroup others
     * @apiDescription  推荐列表
     *
     * @api {get} /recommendations/home　推荐列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiSuccessExample {json} Success-Response 设计师推荐:
     * HTTP/1.1 200 OK
     *{
     *  "data": {
     *    "works": {
     *      "data": [
     *        {
     *          "id": 176,
     *          "user_id": 73,
     *          "title": "水晶印花纯棉连衣裙",
     *          "content": "<p><img src='https://defara.imgix.net/userdata/assets/content/2015/09/30/a1cbd012a431f4e25ad4fd70b3c678348cad9588.jpg'alt='' /></p><p>Land Kay原创水晶印花百搭连衣裙</p><p><img src='http://xiaodong.com/assets/Content/2015/09/29/8ff5ca0bdd8b3f2714d59347bc3c68da880edb34.jpg'alt='' /></p><p><img src='http://xiaodong.com/assets/Content/2015/09/29/219155fdc61cd72de9a19c04c2ee2693e9893515.jpg'alt='' /></p><p><img src='http://xiaodong.com/assets/Content/2015/09/29/db1949ece8e8eba807f6dc7a83ec5e631ffc6f0a.jpg'alt='' /></p><p><img src='http://xiaodong.com/assets/recommend/content/2015/12/10/5ce3e96ce7ce473ac6272dee9221daee.jpg'alt='' /></p>",
     *          "en_content": "<p><img src='https://defara.imgix.net/userdata/assets/content/2015/09/30/a1cbd012a431f4e25ad4fd70b3c678348cad9588.jpg'alt='' /></p><p>Crystals Print Dress crafted in Watercolor Print on Cotton.   \r\n\r\nOur crystals dress is made of soft cotton with an exclusive Land Kay print – perfect for any occasion.\r\n\r\nPair it with strappy sandals for a relaxed daily look.</p><p><img src='http://xiaodong.com/assets/Content/2015/09/29/8ff5ca0bdd8b3f2714d59347bc3c68da880edb34.jpg'alt='' /></p><p><img src='http://xiaodong.com/assets/Content/2015/09/29/219155fdc61cd72de9a19c04c2ee2693e9893515.jpg'alt='' /></p><p><img src='http://xiaodong.com/assets/Content/2015/09/29/db1949ece8e8eba807f6dc7a83ec5e631ffc6f0a.jpg'alt='' /></p><p><img src='http://xiaodong.com/assets/recommend/content/2015/12/10/5ce3e96ce7ce473ac6272dee9221daee.jpg'alt='' /></p>",
     *          "description": "Land Kay原创水晶印花百搭连衣裙",
     *          "cover_picture_url": "https://defara.imgix.net/userdata/assets/content/2015/09/30/a1cbd012a431f4e25ad4fd70b3c678348cad9588.jpg",
     *          "status": "ACTIVE",
     *          "created_at": "2015-09-29 09:45:34",
     *          "updated_at": "2016-03-21 09:12:05",
     *          "recommend_picture_url": "https://defara.s3.amazonaws.com/userdata/assets/recommend/content/2015/12/10/5ce3e96ce7ce473ac6272dee9221daee.jpg",
     *          "user": {
     *            "data": {
     *              "id": 73,
     *              "type": "DESIGNER",
     *              "cellphone": "+420 774 520 198",
     *              "email": "irina.kaygorodova@landkay.com",
     *              "avatar": "https://defara.imgix.net/userdata/assets/avatars/2015/09/29/0a6b803f946279d410e3643a254843c5dce89107.jpg",
     *              "first_name": "Irina",
     *              "last_name": "Kaygorodova",
     *              "gender": "FEMALE",
     *              "is_email_verified": 1,
     *              "is_cellphone_verified": 0,
     *              "created_at": "2015-09-21 11:36:46",
     *              "updated_at": "2016-03-17 06:02:51",
     *              "amount": "0.00",
     *              "status": "ACTIVE",
     *              "nickname": "Irina Kaygorodova",
     *              "birthday": null,
     *              "privilege_amount": 1450,
     *              "position": "",
     *              "is_verify": 1,
     *              "profile": {
     *                "data": {
     *                  "user_id": 73,
     *                  "city_region_id": 0,
     *                  "province_region_id": 0,
     *                  "country_region_id": 1161,
     *                  "page_view": null,
     *                  "description": "我是独立品牌Land Kay的创意总监。得益于我的时尚行业从业经验，以及对布拉格风情、意大利美食的热爱，我现在专注于艺术事业、时尚插画、印花设计、美食、历史建筑、珠宝、书法。作为一名印花设计师，我非常擅长将手绘水彩运用到女装、室内设计中。",
     *                  "position_id": 1,
     *                  "favorite_count": 0,
     *                  "project_count": 0,
     *                  "id": 66,
     *                  "created_at": "2016-03-17 06:00:09",
     *                  "updated_at": null,
     *                  "careers": [
     *                    {
     *                      "place": "时尚杂志",
     *                      "position": "资深设计师"
     *                    }
     *                  ],
     *                  "educations": [
     *                    {
     *                      "school": "",
     *                      "major": ""
     *                    }
     *                  ],
     *                  "country": {
     *                    "data": {
     *                      "id": 1161,
     *                      "name": "捷克共和国",
     *                      "iso3": "CZE",
     *                      "iso2": "CZ"
     *                    }
     *                  },
     *                  "position": {
     *                    "data": {
     *                      "id": 1,
     *                      "name": "设计总监"
     *                    }
     *                  }
     *                }
     *              },
     *              "categories": {
     *                "data": [
     *                  {
     *                    "id": 65,
     *                    "parent_id": 64,
     *                    "name": "印花",
     *                    "parent": {
     *                      "data": {
     *                        "id": 64,
     *                        "parent_id": 0,
     *                        "name": "其他"
     *                      }
     *                    }
     *                  },
     *                  {
     *                    "id": 12,
     *                    "parent_id": 0,
     *                    "name": "女装"
     *                  }
     *                ]
     *              }
     *            }
     *          }
     *        }
     *      ]
     *    },
     *    "makers": {
     *      "data": [
     *        {
     *          "id": 46,
     *          "type": "MAKER",
     *          "cellphone": "13308003957",
     *          "email": "",
     *          "avatar": "https://defara.imgix.net/userdata/assets/avatars/2015/09/29/bac333599ccaea38b5582bcd1761b968813e5491.jpg",
     *          "first_name": "金",
     *          "last_name": "敏",
     *          "gender": "MALE",
     *          "is_email_verified": 0,
     *          "is_cellphone_verified": 0,
     *          "created_at": "2015-09-01 05:47:59",
     *          "updated_at": "2016-03-17 06:02:50",
     *          "amount": "0.00",
     *          "status": "ACTIVE",
     *          "nickname": "米兰亚贝耳服饰",
     *          "birthday": "2015-09-01 00:00:00",
     *          "privilege_amount": 0,
     *          "position": "",
     *          "is_verify": 1,
     *          "recommend_picture_url": "https://defara.imgix.net/userdataassets/recommend/content/2016/02/02/d2b348b59c5b8a525a6c2b4a69ee4d44.jpg",
     *          "categories": {
     *            "data": [
     *              {
     *                "id": 2,
     *                "parent_id": 1,
     *                "name": "西服套装",
     *                "parent": {
     *                  "data": {
     *                    "id": 1,
     *                    "parent_id": 0,
     *                    "name": "男装"
     *                  }
     *                }
     *              },
     *              {
     *                "id": 3,
     *                "parent_id": 1,
     *                "name": "衬衣",
     *                "parent": {
     *                  "data": {
     *                    "id": 1,
     *                    "parent_id": 0,
     *                    "name": "男装"
     *                  }
     *                }
     *              },
     *              {
     *                "id": 7,
     *                "parent_id": 1,
     *                "name": "带帽衫/卫衣",
     *                "parent": {
     *                  "data": {
     *                    "id": 1,
     *                    "parent_id": 0,
     *                    "name": "男装"
     *                  }
     *                }
     *              },
     *              {
     *                "id": 17,
     *                "parent_id": 12,
     *                "name": "半身裙",
     *                "parent": {
     *                  "data": {
     *                    "id": 12,
     *                    "parent_id": 0,
     *                    "name": "女装"
     *                  }
     *                }
     *              },
     *              {
     *                "id": 16,
     *                "parent_id": 12,
     *                "name": "裙装",
     *                "parent": {
     *                  "data": {
     *                    "id": 12,
     *                    "parent_id": 0,
     *                    "name": "女装"
     *                  }
     *                }
     *              }
     *            ]
     *          },
     *          "factory": {
     *            "data": {
     *              "id": 6,
     *              "user_id": 46,
     *              "name": "米兰亚贝耳服饰",
     *              "description": "米兰亚贝耳不同于传统意义上的服装工厂，它在十余载的风风雨雨中成长壮大，但依然保持着最初的那份对服装行业的赤诚之心。工厂的创始人——金老板希望把这家米兰亚贝耳打造成服装行业中的百年老店，因此，与时俱进的设备及技术、始终如一的赤子之心，两者缺一不可。\r\n米兰亚贝耳以深厚的文化积淀为基石，以设计、资讯、原材料来源、技术支持为强有力的后盾，凭借崭新的经营理念、稳健的步伐在市场中脱颖而出，现今已经被众多知名企业指定为定点生产厂家。",
     *              "cover_picture_url": "https://defara.imgix.net/userdata/assets/profile/2016/02/26/1b8798cb7a9066b1b7e43347298accc6af92de5b.jpg",
     *              "contactor": "",
     *              "telephone": "86-133 0800 3957",
     *              "email": "5814269@qq.com",
     *              "empolyee_number": "150",
     *              "mini_order_quantity": 100,
     *              "production_times": 0,
     *              "working_time": "9：00~18：00",
     *              "establish_at": "2000",
     *              "area": "",
     *              "postcode": null,
     *              "address": "",
     *              "country_region_id": 538,
     *              "province_region_id": 926,
     *              "city_region_id": 929,
     *              "created_at": "2016-03-17 06:00:28",
     *              "updated_at": "2016-03-17 06:02:56",
     *              "deleted_at": null,
     *              "ceo_name": "  金敏  ",
     *              "ceo_avatar": "http://xiaodong.com/assets/profile/2015/12/10/627e4d72ec296dc1c70c6dd8d17afb75ee955850.jpg",
     *              "ceo_description": "    学印染出身的金老板，天生就有着对服装的敏感和热情，更拥有常人所不及的独到眼光。他喜欢研究人们的穿着，他觉得这是一件有趣且有意义的事。他认为，“读一件服装就是在阅读一个人的内在。服装是一种语言，是一种文化，把更多的内容放进去，服装就有了灵魂。”  ",
     *              "wechat_no": null,
     *              "top_picture": "http://xiaodong.com/assets/profile/2015/12/10/c7932161de6dbeea43457732290f141e45d51f0e.jpg",
     *              "country": {
     *                "data": {
     *                  "id": 538,
     *                  "name": "中国",
     *                  "iso3": "CHN",
     *                  "iso2": "CN"
     *                }
     *              },
     *              "province": {
     *                "data": {
     *                  "id": 926,
     *                  "name": "四川",
     *                  "iso3": "51",
     *                  "iso2": null
     *                }
     *              },
     *              "city": {
     *                "data": {
     *                  "id": 929,
     *                  "name": "成都",
     *                  "iso3": "1",
     *                  "iso2": null
     *                }
     *              }
     *            }
     *          }
     *        }
     *      ]
     *    }
     *  }
     *}
     */
    public function home()
    {
        $recommendation = new Recommendation();

        return $this->response->item($recommendation, new RecommendTransformer());
    }
    /**
     * @apiGroup others
     * @apiDescription 不同类型的推荐列表
     *
     * @api {get} /recommendations 不同类型的推荐列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String='DESIGNER','MAKER','WORK','SERVICE','designer','maker','work','service'} type 推荐类型,
     * @apiParam {String} [include] 推荐设计师、制造商、work、service时，可引入的关系
     * @apiParam {String} [include.categories]  分类 (推荐设计师、制造商时，可引入的分类)
     * @apiParam {String} [include.profile]     设计师个人信息 (推荐设计师时，可引入的个人信息)
     * @apiParam {String} [include.factory]     制造商的工厂 (推荐制造商时，可引入的工厂)
     * @apiParam {String} [include.attachments] 附件 (推荐work、service时，可引入的附件)
     * @apiParam {String} [include.user]        所属的用户 (推荐work、service时，可引入的用户)
     * @apiParam {String} [include.category]    service的分类 (推荐service时，可引入的服务分类)
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200
     *{
     *  "data": [
     *    {
     *      "id": 4,
     *      "user_id": 153,
     *      "category_id": 2,
     *      "custom_category": null,
     *      "type": "SAMPLE",
     *      "name": "Summer Autumn T-shirt Design",
     *      "content": "",
     *      "attachment_type": "",
     *      "cover_picture_url": "https://defara.imgix.net/userdata/assets/services/16/04/576c671d539bcb7660811fd9b93ca138a7bbf525.jpg",
     *      "duration": 7,
     *      "description": "sketches, spec sheets, sewing instructions, and color ways  for production.\r\n\r\n\r\n\r\n\r\n",
     *      "need_delivery": null,
     *      "is_free": null,
     *      "price": "4480.00",
     *      "status": "ACTIVE",
     *      "visit_count": 0,
     *      "weight": 0,
     *      "updated_at": "2016-04-17 15:06:22",
     *      "created_at": "2016-04-15 02:56:06",
     *      "category_tag": "SINGLE",
     *      "recommend_picture_url": "http://xiaodong.dev/assets/recommend/16/05/15639008685747c7724d009.png",
     *      "status_label": "On the shelf",
     *      "category_tag_label": "Single",
     *      "category": {
     *        "data": {
     *          "id": 2,
     *          "parent_id": 1,
     *          "name": "T-shirts & Blouse ",
     *          "parent_name": "Women's wear"
     *        }
     *      }
     *    },
     *    {
     *      "id": 3,
     *      "user_id": 3,
     *      "category_id": 5,
     *      "custom_category": null,
     *      "type": "SAMPLE",
     *      "name": "2017 SS Women Dresses Design",
     *      "content": "",
     *      "attachment_type": "",
     *      "cover_picture_url": "https://defara.imgix.net/userdata/assets/services/16/04/93b7990c71f3097ea88de9ed3d5e8d75e1b8dc48.jpg",
     *      "duration": 7,
     *      "description": "sketches, spec sheets, sewing instructions, and color ways  for production.\r\n\r\n\r\n\r\n\r\n",
     *      "need_delivery": 0,
     *      "is_free": null,
     *      "price": "4480.00",
     *      "status": "ACTIVE",
     *      "visit_count": 0,
     *      "weight": 0,
     *      "updated_at": "2016-04-17 15:06:21",
     *      "created_at": "2016-04-15 02:49:23",
     *      "category_tag": "SERIES",
     *      "recommend_picture_url": "http://xiaodong.dev/assets/recommend/16/05/14086955235747c6aa7a885.png",
     *      "status_label": "On the shelf",
     *      "category_tag_label": "Collection - 5 styles",
     *      "category": {
     *        "data": {
     *          "id": 5,
     *          "parent_id": 1,
     *          "name": "Dresses",
     *          "parent_name": "Women's wear"
     *        }
     *      }
     *    }
     *  ],
     *  "meta": {
     *    "pagination": {
     *      "total": 2,
     *      "count": 2,
     *      "per_page": 20,
     *      "current_page": 1,
     *      "total_pages": 1,
     *      "links": []
     *    }
     *  }
     *}
     */
    public function index(IndexRequest $request)
    {
        switch ($type = strtoupper($request->get('type'))) {

            case 'DESIGNER':
                $object = new User();
                $table = 'users';
                $transformer = new DesignerTransformer();
                break;
            case 'MAKER':
                $object = new User();
                $table = 'users';
                $transformer = new MakerTransformer();
                break;
            case 'WORK':
                $object = new DesignerWork();
                $table = 'designer_works';
                $transformer = new WorkTransformer();
                break;
            case 'SERVICE':
                $object = new Service();
                $table = 'services';
                $transformer = new ServiceTransformer();
                break;
            default:
                return $this->response->errorNotFound();
                break;
        }

        $recommendations = $object::leftJoin('recommendations', 'recommendations.recommendable_id', '=', $table.'.id')
            ->where('recommendations.type', $type)
            ->where('recommendations.status', 'ACTIVE')
            ->select($table.'.*', 'recommendations.recommend_picture_url')
            ->orderBy('recommendations.weight', 'desc')
            ->paginate($request->get('per_page'));

        return $this->response->paginator($recommendations, $transformer);
    }
}
