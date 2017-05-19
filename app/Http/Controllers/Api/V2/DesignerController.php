<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\DesignerTransformer;
use App\Http\Requests\Api\Authentication\DesignerStoreRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Position;
use App\Models\User;
use App\Models\Style;
use App\Models\Attachment;
use App\Models\ServiceResult;
use App\Models\UserAuthentication;

class DesignerController extends BaseController
{
    /**
     * @apiGroup designer
     * @apiDescription 设计师列表
     *
     * @api {get} /designers 设计师列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String} [include]  可引入的关系
     * @apiParam {String} [include.profile]    设计师的个人信息
     * @apiParam {String} [include.categories] 设计师的设计领域分类
     * @apiParam {String} [include.styles] 设计师风格
     * @apiParam {integer} [perPage] 页码
     * @apiParam {string} [parentCategories] 分类
     * @apiParam {string} [hotCountries] 国家
     * @apiParam {string} [category_ids] 分类 id  (ps 多选以英文逗号分割)
     * @apiParam {string} [country_ids] 国家 id (ps 多选以英文逗号分割)
     * @apiParam {string} [position_ids] 职位 id (ps 多选以英文逗号分割)
     * @apiParam {string} [style_ids] 设计风格 id (ps 多选以英文逗号分割)
     * @apiParam {string} [name] 用户名称
     * @apiParam {String=favorite,sale,review,project,new} [order] 排序方式
     * @apiSampleRequest http://www.defara.com/designers?include=profile,categories
     * @apiSuccessExample {json} Success-Response 设计师列表:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 352,
     *       "type": "DESIGNER",
     *       "cellphone": "",
     *       "email": "info@maurogasperi.com",
     *       "avatar": "https://defara.imgix.net/userdata/assets/avatar/2016/03/09/5d23c3bc24e4d4560e2472fcddaee19f.jpg",
     *       "first_name": "MAURO",
     *       "last_name": "GASPERI",
     *       "gender": "",
     *       "is_email_verified": 0,
     *       "is_cellphone_verified": 0,
     *       "created_at": "2016-03-09 10:14:20",
     *       "updated_at": "2016-03-29 15:58:27",
     *       "amount": "0.00",
     *       "status": "ACTIVE",
     *       "birthday": null,
     *       "is_favorite": false,
     *       "privilege_amount": 0,
     *       "position": "",
     *       "is_verify": 1,
     *       "fullname": "MAURO GASPERI",
     *       "profile": {
     *         "data": {
     *           "id": 239,
     *           "user_id": 352,
     *           "country_region_id": 1608,
     *           "province_region_id": 0,
     *           "city_region_id": 0,
     *           "position_id": 1,
     *           "description": "Mauro Gasper是意大利人，毕业于国际时尚佛罗伦萨大学服装设计和针织设计硕士研究生专业。2008年在布雷西亚自己开设了自己的第一家服装旗舰店，旨在刷新四海一家，全球共享的休闲，随意，自由和当代精髓城市风格。与此同时他本人参加了09年米兰时装周，接下来是将自己的设计作品早世界各地的时装周展示，如东京，基辅，华沙，蒙特卡洛（摩纳哥城市，德国柏林，中国广州和莫斯科等地。Mauro Gasper讲自己在米兰时装周的作品和意大利制造辗转世界各地各大shopping mall，从日本到迪拜，即将登陆中国和美国.",
     *           "personal_page_url": null,
     *           "educations": null,
     *           "en_educations": null,
     *           "careers": null,
     *           "en_careers": null,
     *           "brands": [
     *             "Paola Frani",
     *             "Doratex",
     *             "Cristiano Fissore",
     *             "D&G"
     *           ],
     *           "created_at": "2016-03-29 15:58:12",
     *           "updated_at": "2016-03-29 15:58:12",
     *           "country_name": "意大利",
     *           "address": "意大利"
     *         }
     *       },
     *       "categories": {
     *         "data": [
     *           {
     *             "id": 1,
     *             "parent_id": 0,
     *             "name": "女装",
     *             "children": {
     *               "data": [
     *                 {
     *                   "id": 12,
     *                   "parent_id": 1,
     *                   "name": "泳装"
     *                 }
     *               ]
     *             }
     *           }
     *         ]
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "styles": [
     *  {
     *    "id": 1,
     *    "name": "简约"
     *  },
     *  {
     *    "id": 2,
     *    "name": "复古"
     *  },
     *  {
     *    "id": 3,
     *    "name": "中性"
     *  },
     *],
     *"positions": [
     *  {
     *    "id": 1,
     *    "name": "设计总监",
     *    "key": "creativeDirector"
     *  },
     *  {
     *    "id": 2,
     *    "name": "高级设计师",
     *    "key": "seniorDesigner"
     *  },
     *],
     *     "pagination": {
     *       "total": 63,
     *       "count": 1,
     *       "per_page": 1,
     *       "current_page": 1,
     *       "total_pages": 63,
     *       "links": {
     *         "next": "http://local.defara/api/designers?page=2"
     *       }
     *     }
     *   }
     * }
     */
    public function index(Request $request)
    {
        $users = User::where('users.type', 'DESIGNER');

        $categoryIds = $request->get('category_ids');
        $countryIds = $request->get('country_ids');
        $styleIds = $request->get('style_ids');
        $sort = $request->get('sort');
        $positionIds = $request->get('position_ids');

        if ($categoryIds || $countryIds || $sort || $positionIds) {
            $positionIds = array_filter(array_map('intval', explode(',', $positionIds)));
            $users->leftJoin('designer_profiles', 'users.id', '=', 'designer_profiles.user_id');
            if ($positionIds) {
                $users->whereIn('designer_profiles.position_id', $positionIds);
            }
        }

        // 按分类搜索用户
        $categoryIds = array_filter(array_map('intval', explode(',', $categoryIds)));
        if ($categoryIds) {
            $childrenIds = Category::whereIn('parent_id', $categoryIds)->lists('id')->toArray();
            $categoryIds = array_merge($categoryIds, $childrenIds);
            $users->leftJoin('category_user', 'users.id', '=', 'category_user.user_id')
                ->whereIn('category_user.category_id', $categoryIds)
                ->groupBy('users.id');
        }

        // 按国家搜索用户
        $countryIds = array_filter(array_map('intval', explode(',', $countryIds)));
        if ($countryIds) {
            $users->whereIn('designer_profiles.country_region_id', $countryIds);
        }

        //按风格搜索用户
        $styleIds = array_filter(array_map('intval', explode(',', $styleIds)));
        if ($styleIds) {
            $users->leftJoin('style_user', 'users.id', '=', 'style_user.user_id')
                ->whereIn('style_user.style_id', $styleIds)
                ->groupBy('users.id');
        }

        // 按姓名搜索用户
        if ($name = str_replace(' ', '', $request->get('name'))) {
            $users->where(function ($query) use ($name) {
                $query->where('first_name', 'like', '%'.$name.'%')
                    ->orWhere('last_name', 'like', '%'.$name.'%')
                    ->orWhere('search_name', 'like', '%'.$name.'%');
            });
        }

        //favorite, sale, review, project, new
        $orderBy = 'users.id';

        if ($sort) {
            $sortFiled = [
                'favorite' => 'favorite_count',
                'sale' => 'sale_count',
                'review' => 'page_view',
                'project' => 'project_count',
            ];

            if (array_key_exists($sort, $sortFiled)) {
                $orderby = 'designer_profiles.'.$sort;

                $users->orderBy($orderBy, 'desc');
            }
        } else {
            // 先按weight排序，weight一样了按搜索顺序
            $users->orderBy('weight', 'desc')->orderBy('id', 'desc');
        }

        $users = $users->where('users.status', 'ACTIVE')
            ->where('users.is_verify', 1);

        //是否被登陆用户收藏
        if ($user = $this->user()) {
            $users->with(['followedByCurrentUser' => function ($query) use ($user) {
                $query->where('user_favorites.user_id', $user->id);
            }]);
        }

        $users = $users->select('users.*')->paginate($request->get('per_page'));

        $styles = Style::all();
        $positions = Position::all();

        $response = $this->response->paginator($users, new DesignerTransformer())
            ->addMeta('styles', $styles)
            ->addMeta('positions', $positions);

        if (array_key_exists('hotCountries', $request->all())) {
            $response->addMeta('hotCountries', $this->showHotCountries());
        }
        if (array_key_exists('parentCategories', $request->all())) {
            $response->addMeta('parentCategories', $this->showParentCategory());
        }

        // 纯为了运营逻辑实现的逻辑，没必要，结构不好，没意义
        // 设计师名字查不到东西的时候，查询一下制造商有多少个
        if (!$categoryIds && !$countryIds && !$styleIds && $name && !$users->count()) {
            $makerCount = User::active()
                ->ofType('MAKER')
                ->whereHas('factory', function ($query) use ($name) {
                    $query->where((app()->getLocale() == 'zh') ? 'factories.name' : 'factories.en_name', 'like', '%'.$name.'%');
                })
                ->count();

            $response->addMeta('makerCount', $makerCount);
        }

        // 如果搜索不到结果，TODO 加参数，有参数的时候
        // if (!$users->count()) {
        //     $recommendUsers = User::where('type', 'DESIGNER')
        //         ->has('recommendation')
        //         ->limit(4)
        //         ->get();

        //     // 保证数据正确
        //     if ($recommendUsers->count()) {
        //         $result = $this->response->collection($recommendUsers, new DesignerTransformer());

        //         $content = json_decode($result->morph()->getContent(), true);
        //         $response->addMeta('recommendations', $content);
        //     }
        // }

        return $response;
    }

    private function showHotCountries()
    {
        $number = 5;

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
            $country[] = $countryHot;
        }

        return $country;
    }

    private function showParentCategory()
    {
        return Category::where('parent_id', 0)->get();
    }

    /**
     * @apiGroup designer
     * @apiDescription 设计师详情
     *
     * @api {get} /designers/{id} 设计师详情
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [include]
     * @apiParam {String} [include.profile]    设计师的个人信息
     * @apiParam {String} [include.inquiryServices.categories] 设计师的服务询价分类
     * @apiParam {String} [include.inquiryServices.results] 设计师服务询价结果
     * @apiParam {String} [include.works:limit] works:limit(6) 设计师作品 取6个
     * @apiParam {String} [include.services:limit] services:limit(6) 设计师服务 取6个
     * @apiParam {String} [include.inquiryServiceAttachments:limit] inquiryServiceAttachments:limit(12) 设计师服务询价的附件 取12个
     * @apiParam {String} [include.styles] 设计师风格
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": {
     *    "id": 7,
     *    "user_group_id": 1,
     *    "cellphone": "198789877858",
     *    "email": null,
     *    "avatar": "http://local.defara/assets/avatars/2015/08/14/381289c048d17ea688b62d144d5d02faaa49db58.jpg",
     *    "first_name": "Mill",
     *    "last_name": "Linda",
     *    "gender": "FEMALE",
     *    "is_email_verified": 0,
     *    "is_cellphone_verified": 0,
     *    "created_at": "2015-08-14 10:29:28",
     *    "updated_at": "2015-08-14 14:13:56",
     *    "amount": "0.00",
     *    "status": "ACTIVE",
     *    "nickname": "Linda",
     *    "birthday": "1985-10-17 00:00:00",
     *    "privilege_amount": 0,
     *    "position": "",
     *    "is_verify": 1,
     *    "works": {
     *      "data": [
     *        {
     *          "id": 1,
     *          "user_id": 7,
     *          "title": "JANE纹理真皮尖头高跟凉鞋",
     *          "en_title": "",
     *          "description": null,
     *          "en_description": "",
     *          "cover_picture_url": "http://alpha.defara.com/assets/content/2015/08/14/6fde0681c956931426c2165270499139.jpg",
     *          "status": "ACTIVE",
     *          "created_at": "2015-08-14 10:30:18",
     *          "updated_at": "2015-08-23 02:30:51"
     *        },
     *        {
     *          "id": 76,
     *          "user_id": 7,
     *          "title": "My life",
     *          "en_title": "",
     *          "description": null,
     *          "en_description": "",
     *          "cover_picture_url": "http://alpha.defara.com/assets/content/2015/08/14/ac8a8123f84560d067869d008eebd7d6.png",
     *          "status": "ACTIVE",
     *          "created_at": "2015-08-14 14:14:19",
     *          "updated_at": "2015-08-23 02:31:53"
     *        },
     *      ],
     *      "meta": {
     *        "pagination": {
     *          "total": 4,
     *          "count": 4,
     *          "per_page": 6,
     *          "current_page": 1,
     *          "total_pages": 1,
     *          "links": []
     *        }
     *      }
     *    },
     *    "services": {
     *      "data": [
     *        {
     *          "id": 35,
     *          "user_id": 7,
     *          "category_id": 7,
     *          "custom_category": "",
     *          "type": "CUSTOM",
     *          "service_type": "CUSTOM",
     *          "name": "秋季男士西装设计",
     *          "en_name": "",
     *          "attachment_type": "",
     *          "cover_picture_url": "http://local.defara/assets/service/2015/09/08/b62dc5c4c0d805b5a56e1dd4a3b4266ee55ee7a1.jpg",
     *          "duration": null,
     *          "description": "图案细节：胸前和胳膊上得小鹿、雪梦幻花图案的装饰，使YY充满了浪漫感。 印花细节：大气字母印花装饰，给人与众不同的感觉 细节展示：红色眼镜手绘效果，时尚可爱  细节展示：胸前时尚小标起点缀作用，时尚不呆板  蝴蝶结细节：蝴蝶结设计顽皮典雅 增加了YY的立体感和层次感 时尚大气 印花细节：新型印花型亮片，闪亮效果比传统亮片更闪，且洗涤后也不会脱落 ",
     *          "en_description": "",
     *          "need_delivery": 0,
     *          "is_free": 0,
     *          "price": "0.00",
     *          "status": "INACTIVE",
     *          "visit_count": 0,
     *          "updated_at": "2016-03-14 03:45:47",
     *          "created_at": "2015-09-08 04:25:13"
     *        }
     *      ],
     *      "meta": {
     *        "pagination": {
     *          "total": 3,
     *          "count": 3,
     *          "per_page": 6,
     *          "current_page": 1,
     *          "total_pages": 1,
     *          "links": []
     *        }
     *      }
     *    },
     *"profile": {
     *  "data": {
     *    "id": 242,
     *    "user_id": 355,
     *    "country_region_id": 1,
     *    "province_region_id": 2,
     *    "city_region_id": 0,
     *    "position_id": 1,
     *    "is_luxury": true,//false代表非奢侈品，true代表奢侈品
     *    "description": "hao",
     *    "personal_page_url": "",
     *    "educations": [
     *      {
     *        "school": "",
     *        "major": ""
     *      }
     *    ],
     *    "careers": null,
     *    "brands": [
     *      "cd"
     *    ],
     *    "en_brands": [
     *      "cd"
     *    ],
     *    "created_at": "2016-04-12 12:50:48",
     *    "updated_at": "2016-05-27 12:24:36",
     *    "country_name": "Afghanistan",
     *    "address": "Herat, Afghanistan",
     *    "position": {
     *      "id": 1,
     *      "name": "Creative Director",
     *      "key": "creativeDirector"
     *    }
     *  }
     * },
     *"inquiryServices": {
     *  "data": [
     *    {
     *      "id": 1,
     *      "category_id": 1,
     *      "cover_picture_url": "",
     *      "min_price": "5000.00",
     *      "category_name": "Women's wear",
     *      "categories": {
     *        "data": [
     *          {
     *            "id": 2,
     *            "parent_id": 1,
     *            "name": "T-shirts & Blouse "
     *          },
     *          {
     *            "id": 3,
     *            "parent_id": 1,
     *            "name": "Coats & Jackets"
     *          }
     *        ]
     *      },
     *      "results": {
     *        "data": [
     *          {
     *            "name": "Sketch"
     *          }
     *        ]
     *      }
     *    },
     *    {
     *      "id": 2,
     *      "category_id": 27,
     *      "cover_picture_url": "",
     *      "min_price": "2000.00",
     *      "category_name": "Bags",
     *      "categories": {
     *        "data": [
     *          {
     *            "id": 28,
     *            "parent_id": 27,
     *            "name": "Handbags"
     *          },
     *          {
     *            "id": 29,
     *            "parent_id": 27,
     *            "name": "Cross Body Bags"
     *          }
     *        ]
     *      },
     *      "results": {
     *        "data": []
     *      }
     *    }
     *  ]
     *},
     *"inquiryServiceAttachments": {
     *  "data": [],
     *  "meta": {
     *    "pagination": {
     *      "total": 0,
     *      "count": 0,
     *      "per_page": 20,
     *      "current_page": 1,
     *      "total_pages": 0,
     *      "links": []
     *    }
     *  }
     *},
     *"styles": {
     *  "data": [
     *    {
     *      "id": 1,
     *      "name": "dfds"
     *    }
     *  ]
     *},
     * "meta": {
     *"service_results": [
     *  {
     *    "id": 1,
     *    "name": "Sketch",
     *    "key": "sketch"
     *  },
     *  {
     *    "id": 2,
     *    "name": "Tech Pack",
     *    "key": "sketch"
     *  },
     *  {
     *    "id": 3,
     *    "name": "Original Sample",
     *    "key": "sketch"
     *  },
     *  {
     *    "id": 4,
     *    "name": "Material Sample",
     *    "key": "sketch"
     *  }
     *]
     * }
     *  }
     */
    public function show($id, Request $request)
    {
        $user = User::find($id);

        if (!$user || $user->type != 'DESIGNER') {
            return $this->response->errorForbidden();
        }
        //是否被登陆用户收藏
        if ($loginUser = $this->user()) {
            $followers = $user->followers()->where('user_favorites.user_id', $loginUser->id)->first();
            $user->setRelation('followedByCurrentUser', $followers);
        }

        $serviceResults = ServiceResult::all();

        return $this->response->item($user, new DesignerTransformer($loginUser))->addMeta('service_results', $serviceResults);
    }

    /**
     * @apiGroup designer
     * @apiDescription 设计师认证
     *
     * @api {post} /designer/authentications 设计师认证
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} first_name  第一个名字
     * @apiParam {String} last_name   第二个名字
     * @apiParam {String} avatar      头像base64
     * @apiParam {String} gender      性别 [male,female]
     * @apiParam {Number} position_id    职位
     * @apiParam {String} personal_page_url   设计师个人主页
     * @apiParam {Array} location  位置信息
     * @apiParam {Array} location.country_region_id    国家的id
     * @apiParam {Array} [location.province_region_id] 省份的id
     * @apiParam {Array} [location.city_region_id]     城市的id
     * @apiParam {String} [description]   自我介绍
     * @apiParam {Array} [educations]     教育信息
     * @apiParam {Array} [educations.school]     毕业学校
     * @apiParam {Array} [educations.major]      专业
     * @apiParam {Array} [careers]    工作经历
     * @apiParam {Array} [careers.company]    公司
     * @apiParam {Array} [careers.position]   职位
     * @apiParam {Integer=0，1} brandChoose         是否填写合作品牌， 1 为必填，0 为非必填
     * @apiParam {Array} [brands]             合作品牌
     * @apiParam {Array} [styles]             设计风格 最多5个
     * @apiParam {Array} services   服务
     * @apiParam {Array} [services.works]   服务的作品
     * @apiParam {Array} [services.category_ids]   服务的分类
     * @apiParam {Integer} [services.min_price]   服务最低价格
     * @apiParam {Array} [services.service_results]   服务的设计资料
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function authentication(DesignerStoreRequest $request)
    {
        $validator = \Validator::make($request->all(), [
            'services' => 'required|array|min:1',
        ]);

        $validator->after(function ($validator) use ($request) {
            $services = $request->input('services');
            foreach ($services as $key => $service) {

                if (!is_array($service['category_ids']) || !array_filter($service['category_ids'])) {
                    $validator->errors()->add('category_ids', trans('error.service.category_ids'));
                }

                $ids = isset($service['works']) ? array_column($service['works'], 'id') : [];

                if (!array_filter($ids)) {
                    $validator->errors()->add('works', trans('error.service.works'));
                }

                if (!isset($service['min_price']) || !$service['min_price']) {
                    $validator->errors()->add('min_price', trans('error.service.min_price'));
                }

                if (isset($service['min_price']) && !is_numeric($service['min_price'])) {
                    $validator->errors()->add('min_price', trans('error.service.min_price_incorrect'));
                }

                if (!isset($service['service_results']) || !array_filter((array)$service['service_results'])) {
                    $validator->errors()->add('service_results', trans('error.service.service_results'));
                }
            }

            $educations = $request->input('educations');
            $careers = $request->input('careers');

            // 以后再优化
            array_walk($educations, function(&$element) use ($validator) {
                $count = count(array_filter($element));
                if ($count == 0 || $count == 1) {
                    $validator->errors()->add('educations', trans('error.service.education'));
                }
            });

            array_walk($careers, function(&$element) use ($validator) {
                $count = count(array_filter($element));
                if ($count == 0 || $count == 1) {
                    $validator->errors()->add('educations', trans('error.service.career'));
                }
            });
        });

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages()->all());
        }

        $user = \Auth::user();
        if ($user->type != 'DESIGNER') {
            return $this->response->errorForbidden();
        }

        $infoData = $request->all();
        if (isset($infoData['services'])) {
            $repeatRootCategory = [];
            foreach ($infoData['services'] as $key => $service) {
                $categoryIds = $service['category_ids'];
                $categoryIds = $categoryIds ? array_filter(array_map('intval', $categoryIds)) : [];

                $rootCategories = Category::whereIn('id', $categoryIds)->groupBy('parent_id')->get();
                //判断主分类个数
                if ($rootCategories->count() > 1) {
                    return response()->json(['errors' => ['rootCategory' => trans('error.service.rootCategory')]], 400);
                }

                $rootCategory = $rootCategories->first();
                $childrenCategories = Category::where('parent_id', $rootCategory->parent_id)->whereIn('id', $categoryIds)->get();

                if ($childrenCategories->count() > 5) {
                    return response()->json(['errors' => ['rootCategory' => trans('error.service.childrenCategory')]], 400);
                }

                $infoData['services'][$key]['category_ids'] = $childrenCategories->lists('id')->toArray();
                $infoData['services'][$key]['root_category_id'] = $rootCategory->parent_id;
                $repeatRootCategory[] = $rootCategory->parent_id;
            }
            if (count($repeatRootCategory) != count(array_unique($repeatRootCategory))) {
                return response()->json(['errors' => ['rootCategory' => trans('error.service.repeatRootCategory')]], 400);
            }
        } else {
            $infoData['services']['category_ids'] = '';
            $infoData['services']['works'] = '';
            $infoData['services']['min_price'] = '';
            $infoData['services']['service_results'] = '';
        }

        $infoData = $this->_filterInfo($infoData);

        $user = \Auth::user();
        $authentication = UserAuthentication::firstOrNew(['user_id' => $user->id]);

        $oldWorks = $authentication->info ? array_column($authentication->info['services'], 'works') : [];
        $newWorks = isset($infoData['services']) ? array_column($infoData['services'], 'works'): [];

        $newIds = [];
        foreach ($newWorks as $newWork) {
            if (is_array($newWork)) {
                foreach ($newWork as $attachmentId) {
                    $newIds[] = $attachmentId['id'];
                }
            }
        }

        array_walk($oldWorks, function ($oldWorks) use ($newIds) {
            if (is_array($oldWorks) && !empty($oldWorks)) {
                foreach ($oldWorks as $attachmentId) {
                    if (!in_array($attachmentId['id'], $newIds)) {
                        $attachment = Attachment::find($attachmentId['id']);
                        if ($attachment) {
                            $attachment->delete();
                        }
                    }
                }
            }
        });

        if ($avatar = $request->get('avatar')) {
            $avatar = \Image::make($avatar);

            $fileName = $user->id . '-avatar-' . uniqid() . '.png';
            \File::makeDirectory('assets/avatars/' . date('y/m/'), $mode = 0755, true, true);
            $avatarPath = 'assets/avatars/' . date('y/m/') . $fileName;

            $avatar->save($avatarPath);
            $infoData['avatar'] = $avatarPath;
        }

        $authentication->user_id = $user->id;
        $authentication->info = $infoData;
        $authentication->type = $user->type;
        $authentication->status = 'PENDING';

        if ($authentication->save()) {
            return $this->response->noContent();
        }
    }

    public function _filterInfo($infoData)
    {
        $filterInfo = [];
        $filterInfo['first_name'] = @$infoData['first_name'];
        $filterInfo['last_name'] = @$infoData['last_name'];
        $filterInfo['avatar'] = @$infoData['avatar'];
        $filterInfo['gender'] = @$infoData['gender'];
        $filterInfo['position_id'] = @$infoData['position_id'];
        $filterInfo['personal_page_url'] = @$infoData['personal_page_url'];
        $filterInfo['location']['country_region_id'] = @$infoData['location']['country_region_id'];
        $filterInfo['location']['province_region_id'] = @isset($infoData['location']['province_region_id']) ? $infoData['location']['province_region_id'] : 0;
        $filterInfo['location']['city_region_id'] = @isset($infoData['location']['city_region_id']) ? $infoData['location']['city_region_id'] : 0;

        foreach ((array) $infoData['educations'] as $key => $education) {
            $filterInfo['educations'][$key]['school'] = @$education['school'];
            $filterInfo['educations'][$key]['major'] = @$education['major'];
        }

        foreach ((array) $infoData['careers'] as $key => $experience) {
            $filterInfo['careers'][$key]['company'] = @$experience['company'];
            $filterInfo['careers'][$key]['position'] = @$experience['position'];
        }

        if ($infoData['brandChoose']) {
            $filterInfo['brands'] = @$infoData['brands'];
        } else {
            $filterInfo['brands'] = [];
        }

        $filterInfo['styles'] = $infoData['styles'];
        $filterInfo['description'] = @$infoData['description'];

        if (isset($infoData['services'])) {
            $services = $infoData['services'];

            $allowed = ['id'];
            foreach ($services as $serviceKey => $service) {
                $works = $service['works'];
                foreach ($works as $work) {
                    if (is_array($work)) {
                        array_walk($works, function (&$works) use ($allowed) {
                            $works = array_intersect_key($works, array_flip($allowed));
                        });
                    }
                }
                $services[$serviceKey]['works'] = $works;
            }
        }

        $filterInfo['services'] = $services;

        return $filterInfo;
    }
}
