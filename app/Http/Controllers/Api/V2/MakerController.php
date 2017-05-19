<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\MakerTransformer;
use App\Http\Requests\Api\Authentication\MakerStoreRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Factory;
use App\Models\User;
use App\Models\UserAuthentication;

class MakerController extends BaseController
{
    /**
     * @apiGroup maker
     * @apiDescription 制造商列表
     *
     * @api {get} /makers 制造商列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String} [include]  可引入的关系
     * @apiParam {String} [include.categories]  制造商分类
     * @apiParam {String} [include.factory]  制造商工厂
     * @apiParam {integer} [min_employee] 最小员工数
     * @apiParam {integer} [max_employee] 最大员工数
     * @apiParam {integer} [per_page] 页码
     * @apiParam {string} [parentCategories] 分类
     * @apiParam {integer} [min_moq] 最小起订量
     * @apiParam {integer} [max_moq] 最大起订量
     * @apiParam {String} [category_ids] 分类 id
     * @apiParam {String=favorite,sale,review,project,new} [order] 排序方式
     * @apiSampleRequest http://www.defara.com/makers?include=categories,factory
     * @apiSuccessExample {json} Success-Response 工厂列表:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 311,
     *       "type": "MAKER",
     *       "cellphone": "18908185339",
     *       "email": "",
     *       "avatar": null,
     *       "first_name": "林",
     *       "last_name": "玉春",
     *       "gender": "MALE",
     *       "is_email_verified": 0,
     *       "is_cellphone_verified": 0,
     *       "created_at": "2016-01-28 04:08:23",
     *       "updated_at": "2016-03-30 06:15:43",
     *       "amount": "0.00",
     *       "status": "ACTIVE",
     *       "is_favorite": false,
     *       "birthday": "0000-00-00 00:00:00",
     *       "privilege_amount": 0,
     *       "position": "",
     *       "is_verify": 1,
     *       "fullname": "林 玉春",
     *       "categories": {
     *         "data": [
     *           {
     *             "id": 27,
     *             "parent_id": 0,
     *             "name": "包袋",
     *             "children": {
     *               "data": [
     *                 {
     *                   "id": 35,
     *                   "parent_id": 27,
     *                   "name": "背包"
     *                 }
     *               ]
     *             }
     *           }
     *         ]
     *       },
     *       "factory": {
     *         "data": {
     *           "id": 35,
     *           "user_id": 311,
     *           "name": "三德鞋业",
     *           "description": "三德鞋业创立于2009年，专注于设计、生产精品鞋履，自有60亩厂房。短短几年时间，三德鞋业已发展成了集研发、生产、定制、销售、服务为一体的时尚企业，在销售额、影响力、渠道网络等方面，都处于行业领先位置。在国际品牌化经营的驱动下，三德如今已拥有了多个自主品牌，“艾米奇”是其中的佼佼者。艾米奇不断与时俱进，先后获得“四川省名牌”、“国家质检优等产品”、最具影响力十大品牌”、“消费者最喜爱品牌”、 “十大特色品牌”等多项荣誉。",
     *           "cover_picture_url": "https://defara.imgix.net/userdata/assets/profile/2016/01/28/e631ffa6d4524b06a208758f4b3aaa0ff37f2230.jpg",
     *           "contactor_name": "林玉春",
     *           "en_contactor_name": "林玉春",
     *           "contactor_avatar": "https://defara.imgix.net/userdata/assets/profile/2016/01/28/0b24dbb9486688dc0200a94915dc9cdfa1764cf5.jpg",
     *           "contactor_position": "",
     *           "en_contactor_position": "",
     *           "telephone": "86-4006-3333-59",
     *           "email": "aimiqi@aimiqi.com",
     *           "empolyee_number": "500",
     *           "mini_order_quantity": 100,
     *           "production_times": 0,
     *           "working_time": "08：00-18：00",
     *           "sample_cycle": "",
     *           "establish_at": "2009",
     *           "area": "",
     *           "postcode": null,
     *           "address": "中国, 四川, 成都",
     *           "country_region_id": 538,
     *           "province_region_id": 926,
     *           "city_region_id": 929,
     *           "brands": [
     *             "Aimickey"
     *           ],
     *           "created_at": "2016-03-30 06:15:47",
     *           "updated_at": "2016-03-30 06:16:05",
     *           "deleted_at": null
     *         }
     *       }
     *     }
     *   ],
     *"meta": {
     *     "parentCategories": [
     *  {
     *    "id": 1,
     *    "parent_id": 0,
     *    "name": "女装",
     *    "icon_url": "http://xiaodong.dev/assets/categories/16/06/182780977057611421206b7.jpg"
     *  }
     *],
     *     "pagination": {
     *       "total": 27,
     *       "count": 1,
     *       "per_page": 1,
     *       "current_page": 1,
     *       "total_pages": 27,
     *       "links": {
     *         "next": "http://local.defara/api/makers?page=2"
     *       }
     *     }
     *   }
     * }
     */
    public function index(Request $request)
    {
        $users = User::where('users.type', 'MAKER')->where('users.status', 'ACTIVE');

        $categoryIds = $request->get('category_ids');

        $categoryIds = array_filter(array_map('intval', explode(',', $categoryIds)));
        if ($categoryIds) {
            $childrenIds = Category::whereIn('parent_id', $categoryIds)->lists('id')->toArray();

            $categoryIds = array_merge($categoryIds, $childrenIds);

            $users->leftJoin('category_user', 'users.id', '=', 'category_user.user_id')
                ->whereIn('category_user.category_id', $categoryIds);
        }

        // 按姓名搜索用户
        if ($name = trim($request->get('name'))) {
            $users->whereHas('factory', function ($query) use ($name) {
                $query->where((app()->getLocale() == 'zh') ? 'factories.name' : 'factories.en_name', 'like', '%'.$name.'%');
            });
        }

        //员工数量
        $minEmployee = (int) $request->get('min_employee');
        $maxEmployee = (int) $request->get('max_employee');
        //最小起订量
        $minMoq = (int) $request->get('min_moq');
        $maxMoq = (int) $request->get('max_moq');
        //favorite, sale, review, project, new
        $sort = $request->get('sort');

        if ($categoryIds || $minEmployee || $maxEmployee || $minMoq || $maxMoq || $sort) {
            $users->leftJoin('factories', 'factories.user_id', '=', 'users.id');
        }

        if ($minEmployee) {
            $users->where('factories.empolyee_number', '>=', $minEmployee);
        }
        if ($maxEmployee) {
            $users->where('factories.empolyee_number', '<=', $maxEmployee);
        }

        if ($minMoq) {
            $users->where('factories.mini_order_quantity', '>=', $minMoq);
        }
        if ($maxMoq) {
            $users->where('factories.mini_order_quantity', '<=', $maxMoq);
        }

        $orderBy = 'users.id';
        if ($sort) {
            $sortFiled = [
                'favorite' => 'favorite_count',
                'sale' => 'sale_count',
                'review' => 'page_view',
                'project' => 'project_count',
            ];

            if (array_key_exists($sort, $sortFiled)) {
                $orderby = 'factories.'.$sort;

                $users->orderBy($orderBy, 'desc');
            }
        } else {
            $users->orderBy('weight', 'desc')->orderBy('id', 'desc');
        }

        $users = $users->select('users.*')
            ->where('users.status', 'ACTIVE');
        //是否被登陆用户收藏
        if ($user = $this->user()) {
            $users->with(['followedByCurrentUser' => function ($query) use ($user) {
                $query->where('user_favorites.user_id', $user->id);
            }]);
        }

        $users = $users->where('users.is_verify', 1)
            ->groupBy('users.id')
            ->paginate();

        $response = $this->response->paginator($users, new MakerTransformer());

        // 如果搜索不到结果，TODO 加参数，有参数的时候增加
        // if (!$users->count()) {
        //     $recommendUsers = User::where('type', 'MAKER')
        //         ->has('recommendation')
        //         ->limit(4)
        //         ->get();

        //     // 保证数据正确
        //     if ($recommendUsers->count()) {
        //         $result = $this->response->collection($recommendUsers, new MakerTransformer());

        //         $content = json_decode($result->morph()->getContent(), true);
        //         $response->addMeta('recommendations', $content);
        //     }
        // }
        if (array_key_exists('parentCategories', $request->all())) {
            $response->addMeta('parentCategories', $this->showParentCategory());
        }

        return $response;
    }

    private function showParentCategory()
    {
        return Category::where('parent_id', 0)->get();
    }

    /**
     * @apiGroup maker
     * @apiDescription 制造商详情
     *
     * @api {get} /makers/{id} 制造商详情
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [include]  可引入的关系
     * @apiParam {string} [include.categories]  制造商分类
     * @apiParam {string} [include.factory]     制造商工厂
     * @apiParam {string} [include.factory.contents] 工厂内容（图片，工艺，报告）
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": {
     *    "id": 80,
     *    "user_group_id": 2,
     *    "cellphone": "nibaba",
     *    "email": null,
     *    "avatar": null,
     *    "first_name": null,
     *    "last_name": null,
     *    "gender": null,
     *    "is_email_verified": 0,
     *    "is_cellphone_verified": 0,
     *    "created_at": "2015-12-09 12:03:35",
     *    "updated_at": "2015-12-09 12:03:42",
     *    "amount": "0.00",
     *    "status": "ACTIVE",
     *    "nickname": null,
     *    "birthday": null,
     *    "privilege_amount": 0,
     *    "position": "",
     *    "is_verify": 1,
     *    "categories": {
     *      "data": [
     *        {
     *          "id": 64,
     *          "parent_id": 0,
     *          "name": "其他"
     *        },
     *        {
     *          "id": 42,
     *          "parent_id": 0,
     *          "name": "包袋"
     *        },
     *        {
     *          "id": 44,
     *          "parent_id": 42,
     *          "name": "手抓包",
     *          "parent": {
     *            "data": {
     *              "id": 42,
     *              "parent_id": 0,
     *              "name": "包袋"
     *            }
     *          }
     *        },
     *        {
     *          "id": 50,
     *          "parent_id": 42,
     *          "name": "背包",
     *          "parent": {
     *            "data": {
     *              "id": 42,
     *              "parent_id": 0,
     *              "name": "包袋"
     *            }
     *          }
     *        }
     *      ]
     *    },
     *    "brands": {
     *      "data": [
     *        {
     *          "id": 18,
     *          "user_id": 80,
     *          "name": "shadhiAHD"
     *        }
     *      ]
     *    },
     *    "factory": {
     *      "data": {
     *        "id": 21,
     *        "user_id": 80,
     *        "name": "家具啊",
     *        "en_name": "saaads",
     *        "description": "看见啊换卡后打开的电话卡电脑吧\r\n大哭大闹",
     *        "en_description": "dsdsafsarewffffffffffffffffffffffffffffffffffffffffffffffffff",
     *        "cover_picture_url": "http://xiaodong.com/assets/profile/2015/12/10/37c4e66b99c11e9456691a4ee3915668ddbda02d.jpg",
     *        "contactor": "",
     *        "telephone": null,
     *        "email": null,
     *        "empolyee_number": "1222",
     *        "mini_order_quantity": 1222,
     *        "production_times": 0,
     *        "working_time": null,
     *        "brief": null,
     *        "establish_at": "",
     *        "area": "",
     *        "postcode": null,
     *        "address": "",
     *        "country_region_id": 20,
     *        "province_region_id": 22,
     *        "city_region_id": 0,
     *        "created_at": "2016-03-15 02:43:52",
     *        "updated_at": "2016-03-15 02:43:52",
     *        "ceo_name": "寒冰",
     *        "ceo_name_en": "coco",
     *        "ceo_avatar": "http://xiaodong.com/assets/profile/2015/12/10/231d09baf17d9cf5100ddf1822eb3f98e70bb12e.jpg",
     *        "ceo_description": "蝴蝶王和我拜佛很少看你分开买撒 ",
     *        "ceo_description_en": "dsadsadwq",
     *        "wechat_no": null,
     *        "top_picture": "http://xiaodong.com/assets/profile/2015/12/10/db0b08da20b18f21d53f7fffe78800d7f950a4f9.jpg",
     *        "country": {
     *          "id": 20,
     *          "name": "阿尔及利亚",
     *          "en_name": "Algeria",
     *          "iso3": "DZA",
     *          "iso2": "DZ"
     *        },
     *        "province": {
     *          "id": 22,
     *          "name": "艾因·德夫拉",
     *          "en_name": "Ain Defla",
     *          "iso3": "ADE",
     *          "iso2": null
     *        },
     *        "city": null
     *      }
     *    },
     *   "contents": {
     *     "data": [
     *       {
     *         "id": 280,
     *         "title": null,
     *         "description": null,
     *         "cover_picture_url": "https://defara.imgix.net/userdata/assets/profile/2016/01/28/5871c6812999b42b7d205fc9fcffbbd89b5888fc.jpg",
     *         "type": "PHOTO"
     *       },
     *       {
     *         "id": 281,
     *         "title": null,
     *         "description": null,
     *         "cover_picture_url": "https://defara.imgix.net/userdata/assets/profile/2016/01/28/fa0b66bcfb8ce650adb135c7c79954f7d3e711f9.jpg",
     *         "type": "PHOTO"
     *       },
     *       {
     *         "id": 282,
     *         "title": null,
     *         "description": null,
     *         "cover_picture_url": "https://defara.imgix.net/userdata/assets/profile/2016/01/28/f59ace710563f9cd094466d3d387c9f2b9865338.jpg",
     *         "type": "PHOTO"
     *       }
     *     ]
     *   }
     *  }
     *}
     */
    public function show($id, Request $request)
    {
        $maker = User::find($id);

        if (!$maker) {
            return $this->response->errorNotFound();
        }

        //是否被登陆用户收藏
        if ($loginUser = $this->user()) {
            $followers = $maker->followers()->where('user_favorites.user_id', $loginUser->id)->first();
            $maker->setRelation('followedByCurrentUser', $followers);
        }

        return $this->response->item($maker, new MakerTransformer());
    }

    /**
     * @apiGroup maker
     * @apiDescription 制造商认证
     *
     * @api {post} /maker/authentications 制造商认证
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} factory_name  工厂名称
     * @apiParam {Array} [location]  地址信息
     * @apiParam {Array} [location.country_region_id]  国家的id
     * @apiParam {Array} [location.province_region_id] 省份的id
     * @apiParam {Array} [location.city_region_id]     城市的id
     * @apiParam {Array} [location.detail]            详细地址
     * @apiParam {String} contact              联系人
     * @apiParam {String} contact_cellphone    联系人电话
     * @apiParam {String} [email]    邮件
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function authentication(MakerStoreRequest $request)
    {
        $user = \Auth::user();
        if ($user->type != 'MAKER') {
            return $this->response->errorForbidden();
        }
        // TODO 这里有问题
        $infoData = $this->_filterInfo($request->all());

        $authentication = UserAuthentication::firstOrNew(['user_id' => $user->id]);

        $authentication->user_id = $user->id;
        $authentication->info = $infoData;
        $authentication->type = $user->type;

        if (!$authentication->save()) {
            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }

    public function _filterInfo($infoData)
    {
        $filterInfo = [];
        $filterInfo['factory_name'] = @$infoData['factory_name'];
        $filterInfo['contact'] = @$infoData['contact'];
        $filterInfo['contact_cellphone'] = @$infoData['contact_cellphone'];
        $filterInfo['email'] = @$infoData['email'];
        $filterInfo['location']['country_region_id'] = @$infoData['location']['country_region_id'];
        $filterInfo['location']['province_region_id'] = @$infoData['location']['province_region_id'];
        $filterInfo['location']['city_region_id'] = @$infoData['location']['city_region_id'];
        $filterInfo['location']['detail'] = @$infoData['location']['detail'];

        return $filterInfo;
    }
}
