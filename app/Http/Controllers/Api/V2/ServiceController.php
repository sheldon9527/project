<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\ServiceTransformer;
use App\Http\Requests\Api\Service\IndexRequest;
use App\Http\Requests\Api\Service\StoreRequest;
use App\Http\Requests\Api\Service\UpdateRequest;
use App\Http\Requests\Api\Service\UserIndexRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Repositories\Contracts\ServiceRepositoryContract;

class ServiceController extends BaseController
{
    /**
     * @apiGroup designer
     * @apiDescription 某个设计师的服务列表
     *
     * @api {get} /designers/{id}/services 某个设计师的服务列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [perPage]  可选分页
     * @apiParam {String} [name] 关键字，搜索服务名称
     * @apiParam {string} [include]  可引入的关系
     * @apiParam {string} [include.category]  服务的分类
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 3,
     *       "user_id": 7,
     *       "category_id": 1,
     *       "custom_category": null,
     *       "type": "SAMPLE",
     *       "name": null,
     *       "attachment_type": "",
     *       "cover_picture_url": null,
     *       "duration": null,
     *       "description": null,
     *       "need_delivery": 0,
     *       "is_free": 0,
     *       "price": "0.00",
     *       "status": "ACTIVE",
     *       "visit_count": 0,
     *       "updated_at": "2016-03-14 06:38:23",
     *       "created_at": "2015-08-26 03:16:43",
     *       "category": {
     *         "data": {
     *           "id": 1,
     *           "parent_id": 0,
     *           "name": "男装"
     *         }
     *       }
     *     },
     *     {
     *       "id": 4,
     *       "user_id": 7,
     *       "category_id": 0,
     *       "custom_category": null,
     *       "type": "PRODUCTION",
     *       "name": null,
     *       "attachment_type": "",
     *       "cover_picture_url": null,
     *       "duration": null,
     *       "description": null,
     *       "need_delivery": 0,
     *       "is_free": 0,
     *       "price": "0.00",
     *       "status": "ACTIVE",
     *       "visit_count": 0,
     *       "updated_at": "2016-03-14 06:38:23",
     *       "created_at": "2015-08-26 03:16:43",
     *       "category": null
     *     }
     *   ],
     *   "meta": {
     *     "pagination": {
     *       "total": 4,
     *       "count": 4,
     *       "per_page": 6,
     *       "current_page": 1,
     *       "total_pages": 1,
     *       "links": []
     *     }
     *   }
     * }
     */
    public function designerIndex($id, Request $request)
    {
        $user = User::find($id);

        $this->authorizeForUser($user, 'userIndex', Service::class);

        $services = $user->services();

        if ($name = $request->get('name')) {
            $services->where((app()->getLocale() == 'zh') ? 'name' : 'en_name', 'like', '%'.$name.'%');
        }

        //是否被登陆用户收藏
        if ($loginUser = $this->user()) {
            $services->with(['followedByCurrentUser' => function ($query) use ($loginUser) {
                $query->where('user_favorites.user_id', $loginUser->id);
            }]);
        }

        $services = $services->active()
            ->orderBy('weight', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate($request->get('per_page'));

        return $this->response->paginator($services, new ServiceTransformer());
    }

    /**
     * @apiGroup service
     * @apiDescription 设计师所有服务列表
     *
     * @api {get} /services 设计师所有服务列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [include]  可引入的关系
     * @apiParam {string} [include.user]  服务所属的用户
     * @apiParam {string} [include.category]  服务的分类
     * @apiParam {integer} [page] 页码
     * @apiParam {integer} [min_price]    最小价格
     * @apiParam {integer} [max_price]    最大价格
     * @apiParam {integer} [category_tag] 产品属性 (单品 SINGLE / 系列 SERIES)
     * @apiParam {String} [keyword] 关键字，搜索服务名称
     * @apiParam {string} [category_ids] 分类 id  (ps 多选以英文逗号分割)
     * @apiSampleRequest http://www.defara.com/user/services?include=user,category
     * @apiSuccessExample {json} Success-Response 服务列表:
     * HTTP/1.1 200 OK
     *
     *   {
     *     "data": [
     *       {
     *         "id": 40,
     *         "user_id": 33,
     *         "category_id": 11,
     *         "custom_category": "1",
     *         "custom_service_name": "设计",
     *         "custom_design_document": "",
     *         "service_picture": "http://dev.defara.com/assets/service/2015/09/16/53262643ea51ad99b8492cd54d79113bea7d77eb.png",
     *         "service_date": null,
     *         "description": "设计T-shirt",
     *         "is_required_delivery_service": null,
     *         "is_free": null,
     *         "is_favorite": false,
     *         "status": "ACTIVE",
     *         "updated_at": null,
     *         "created_at": "2015-09-16 06:27:24",
     *         "is_buy": 1,
     *         "review": "",
     *         "custom_service_price": "",
     *         "user": {
     *           "data": {
     *             "id": 33,
     *             "user_group_id": 1,
     *             "cellphone": "13438825535",
     *             "email": "stacy.xu@defara.com",
     *             "avatar": "http://dev.defara.com/assets/avatars/2015/08/25/55326bea7fcdcf46542288d044368bf4d4ec5c4a.jpg",
     *             "first_name": "",
     *             "last_name": "",
     *             "gender": "FEMALE",
     *             "is_email_verified": 1,
     *             "is_cellphone_verified": 1,
     *             "created_at": "2015-08-25 07:24:31",
     *             "updated_at": "2015-09-29 06:09:32",
     *             "amount": "0.89",
     *             "status": "ACTIVE",
     *             "nickname": "Stacy",
     *             "birthday": "0000-00-00 00:00:00",
     *             "privilege_amount": 0,
     *             "position": "",
     *             "is_verify": 1
     *           }
     *         },
     *         "category": {
     *           "data": {
     *             "id": 11,
     *             "parent_id": 1,
     *             "name": "泳装",
     *             "parent": {
     *               "data": {
     *                 "id": 1,
     *                 "parent_id": 0,
     *                 "name": "男装"
     *               }
     *             }
     *           }
     *         }
     *       },
     *       {
     *         "id": 101,
     *         "user_id": 78,
     *         "category_id": 5,
     *         "custom_category": null,
     *         "custom_service_name": "2",
     *         "custom_design_document": "png    pdf",
     *         "service_picture": "http://dev.defara.com/assets/service/2015/12/09/33f6d2a711c95762a00a4f270ef6937d01d47cf6.jpg",
     *         "service_date": null,
     *         "description": "日聚会上，爸爸总显得有点不得其所。他不是忙于吹气球，就是摆日聚会上，爸爸总显得有点不得其所。他不是忙于吹气球，就是摆日聚会上，爸爸总显得有点不得其所。他不是忙于吹气球，就是摆日聚会上，爸爸总显得有点不得其所。他不是忙于吹气球，就是摆",
     *         "is_required_delivery_service": 1,
     *         "is_free": 1,
     *         "status": "ACTIVE",
     *         "updated_at": null,
     *         "created_at": "2015-12-09 06:23:17",
     *         "is_buy": 1,
     *         "review": "",
     *         "custom_service_price": "",
     *         "user": {
     *           "data": {
     *             "id": 78,
     *             "user_group_id": 1,
     *             "cellphone": "**********",
     *             "email": "53245211@qq.com",
     *             "avatar": "http://dev.defara.com/assets/avatar/2015/12/09/bc9ddccba2fc11bcecfed69aa8867328.jpg",
     *             "first_name": "2",
     *             "last_name": "2",
     *             "gender": "FEMALE",
     *             "is_email_verified": 0,
     *             "is_cellphone_verified": 0,
     *             "created_at": "2015-12-09 03:12:58",
     *             "updated_at": "2016-01-21 07:37:30",
     *             "amount": "0.00",
     *             "status": "ACTIVE",
     *             "nickname": "你猜啊",
     *             "birthday": null,
     *             "privilege_amount": 0,
     *             "position": "",
     *             "is_verify": 1
     *           }
     *         },
     *         "category": {
     *           "data": {
     *             "id": 5,
     *             "parent_id": 1,
     *             "name": "外套/夹克",
     *             "parent": {
     *               "data": {
     *                 "id": 1,
     *                 "parent_id": 0,
     *                 "name": "男装"
     *               }
     *             }
     *           }
     *         }
     *       }
     *     ],
     *     "meta": {
     *       "pagination": {
     *         "total": 2,
     *         "count": 2,
     *         "per_page": 20,
     *         "current_page": 1,
     *         "total_pages": 1,
     *         "links": []
     *       }
     *     }
     *   }
     */
    public function index(IndexRequest $request)
    {
        $services = Service::whereHas('user', function ($query) {
            $query->where('users.status', 'ACTIVE');
        });

        if ($name = $request->get('name')) {
            $services->where((app()->getLocale() == 'zh') ? 'name' : 'en_name', 'like', '%'.$name.'%');
        }

        $services = $this->searchService($services, $request);

        $services = $services->active();
        //是否被登陆用户收藏
        if ($loginUser = $this->user()) {
            $services->with(['followedByCurrentUser' => function ($query) use ($loginUser) {
                $query->where('user_favorites.user_id', $loginUser->id);
            }]);
        }

        $services = $services->orderBy('updated_at', 'desc')->paginate();

        $response = $this->response->paginator($services, new ServiceTransformer());

        // 如果搜索不到结果，
        if (!$services->count()) {
            $recommendServices = Service::limit(4)->get();
            // 保证数据正确
            if ($recommendServices->count()) {
                $result = $this->response->collection($recommendServices, new ServiceTransformer());

                $content = json_decode($result->morph()->getContent(), true);
                $response->addMeta('recommendations', $content);
            }
        }

        return $response;
    }

    /**
     * @apiGroup designer
     * @apiDescription 服务详情
     *
     * @api {get} /services/{id} 服务详情
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [include]  可引入的关系
     * @apiParam {string} [include.user]  服务所属的用户
     * @apiParam {string} [include.more]  当前用户的更多服务
     * @apiParam {string} [include.category]  服务的分类
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *   {
     *       "data": {
     *           "id": 29,
     *           "user_id": 94,
     *           "category_id": 32,
     *           "custom_category": "<script>alert('ss')</script>",
     *           "type": "CUSTOM",
     *           "name": "<script>alert('ss')</script>",
     *           "attachment_type": "",
     *           "cover_picture_url": "http://dev.defara.com/assets/service/2015/09/30/45aa2459695e6334179270933511c3a7348d6e39.gif",
     *           "duration": null,
     *           "description": "<script>alert('ss')</script>",
     *           "need_delivery": 0,
     *           "is_free": 0,
     *           "price": "0.00",
     *           "status": "ACTIVE",
     *           "visit_count": 0,
     *           "updated_at": "2016-03-18 02:57:04",
     *           "created_at": "2015-09-30 08:02:00",
     *           "category": {
     *               "data": {
     *                   "id": 32,
     *                   "parent_id": 27,
     *                   "name": "凉鞋",
     *                   "parent": {
     *                       "data": {
     *                       "id": 27,
     *                       "parent_id": 0,
     *                       "name": "男鞋"
     *                       }
     *                   }
     *               }
     *           },
     *           "user": {
     *               "data": {
     *                   "id": 94,
     *                   "type": "DESIGNER",
     *                   "cellphone": "",
     *                   "email": "im@fuck.you",
     *                   "avatar": "http://dev.defara.com/assets/avatars/2015/09/30/da604b66ba32729070ce7cd7d1a9ce509e82213b.gif",
     *                   "first_name": "",
     *                   "last_name": "",
     *                   "gender": "",
     *                   "is_email_verified": 1,
     *                   "is_cellphone_verified": 0,
     *                   "created_at": "2015-09-30 07:36:49",
     *                   "updated_at": "2016-03-18 02:57:13",
     *                   "amount": "0.00",
     *                   "status": "INACTIVE",
     *                   "nickname": "<script>alert('ss')</script>",
     *                   "birthday": "0000-00-00 00:00:00",
     *                   "privilege_amount": 0,
     *                   "position": "",
     *                   "is_verify": 1
     *               }
     *           },
     *           "more": {
     *               "data": [
     *                   {
     *                   "id": 113,
     *                   "user_id": 94,
     *                   "category_id": 32,
     *                   "custom_category": "zidingyi fenlei",
     *                   "type": "CUSTOM",
     *                   "name": "name",
     *                   "attachment_type": "",
     *                   "cover_picture_url": "https://defara.imgix.net/userdata/assets/service/2016/03/09/0f8f8c9e855cdcf7688f30d448deae921f40c8e5.jpg",
     *                   "duration": null,
     *                   "description": "描述",
     *                   "need_delivery": 0,
     *                   "is_free": 0,
     *                   "price": "0.00",
     *                   "status": "ACTIVE",
     *                   "visit_count": 0,
     *                   "updated_at": "2016-03-22 14:03:36",
     *                   "created_at": "2016-03-22 06:03:47"
     *                   },
     *                   {
     *                   "id": 114,
     *                   "user_id": 94,
     *                   "category_id": 32,
     *                   "custom_category": "zidingyi fenlei",
     *                   "type": "CUSTOM",
     *                   "name": "name",
     *                   "attachment_type": "",
     *                   "cover_picture_url": "https://defara.imgix.net/userdata/assets/service/2016/03/09/0f8f8c9e855cdcf7688f30d448deae921f40c8e5.jpg",
     *                   "duration": null,
     *                   "description": "描述",
     *                   "need_delivery": 0,
     *                   "is_free": 0,
     *                   "price": "0.00",
     *                   "status": "ACTIVE",
     *                   "visit_count": 0,
     *                   "updated_at": "2016-03-22 14:03:36",
     *                   "created_at": "2016-03-22 06:04:28"
     *                   },
     *                   {
     *                   "id": 115,
     *                   "user_id": 94,
     *                   "category_id": 32,
     *                   "custom_category": "zidingyi fenlei",
     *                   "type": "CUSTOM",
     *                   "name": "name",
     *                   "attachment_type": "",
     *                   "cover_picture_url": "https://defara.imgix.net/userdata/assets/service/2016/03/09/0f8f8c9e855cdcf7688f30d448deae921f40c8e5.jpg",
     *                   "duration": null,
     *                   "description": "描述",
     *                   "need_delivery": 0,
     *                   "is_free": 0,
     *                   "price": "0.00",
     *                   "status": "ACTIVE",
     *                   "visit_count": 0,
     *                   "updated_at": "2016-03-22 14:03:36",
     *                   "created_at": "2016-03-22 06:04:55"
     *                   },
     *                   {
     *                   "id": 116,
     *                   "user_id": 94,
     *                   "category_id": 32,
     *                   "custom_category": "zidingyi fenlei",
     *                   "type": "CUSTOM",
     *                   "name": "name",
     *                   "attachment_type": "",
     *                   "cover_picture_url": "https://defara.imgix.net/userdata/assets/service/2016/03/09/0f8f8c9e855cdcf7688f30d448deae921f40c8e5.jpg",
     *                   "duration": null,
     *                   "description": "描述",
     *                   "need_delivery": 0,
     *                   "is_free": 0,
     *                   "price": "0.00",
     *                   "status": "ACTIVE",
     *                   "visit_count": 0,
     *                   "updated_at": "2016-03-22 14:03:36",
     *                   "created_at": "2016-03-22 06:04:56"
     *                   }
     *               ]
     *           }
     *       }
     *   }
     */
    public function show($id, Request $request)
    {
        $service = Service::find($id);

        if (!$service) {
            return $this->response->errorNotFound();
        }

        //是否被登陆用户收藏
        if ($loginUser = $this->user()) {
            $followers = $service->followers()->where('user_favorites.user_id', $loginUser->id)->first();
            $service->setRelation('followedByCurrentUser', $followers);
        }

        return $this->response->item($service, new ServiceTransformer($loginUser));
    }

    /**
     * @apiGroup designer
     * @apiDescription 添加服务
     *
     * @api {post} /services 添加服务
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} name 服务名称
     * @apiParam {String} category_tag 单品(SINGLE)/系列(SERIES)
     * @apiParam {String} [category_id]  分类id(单品)
     * @apiParam {String} [price]        价格
     * @apiParam {String} [duration]     所需时间
     * @apiParam {String} [cover_picture_url] 封面图片地址 tag =cover
     * @apiParam {Array} [attachments]    详情图 tag= detail
     * @apiParam {String} [description]       服务描述
     * @apiParam {String} [custom_category]   自定义分类
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 created
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::user();

        $this->authorize('store', Service::class);

        $service = new Service();
        $service->fill($request->all());

        $coverPictureUrl = $request->get('cover_picture_url');
        $service->cover_picture_url = $coverPictureUrl ? parse_url($coverPictureUrl)['path'] : '';

        $service->user()->associate($user);
        $service->category_id = $request->get('category_tag') == 'SINGLE' ? $request->get('category_id') : 0;

        $service->save();

        if ($attachments = $request->get('attachments')) {
            $service->updateAttachment($attachments, 'detail');
        }

        return $this->response->created();
    }

    /**
     * @apiGroup designer
     * @apiDescription 当前设计师的服务列表
     *
     * @api {get} /user/services 当前设计师的服务列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} [include]  可引入的关系
     * @apiParam {String} [include.user]  服务所属的用户
     * @apiParam {String} [include.category]  服务的分类
     * @apiParam {integer} [page] 页码
     * @apiParam {integer} [min_price]    最小价格
     * @apiParam {integer} [max_price]    最大价格
     * @apiParam {String} [category_tag] 产品属性 (单品 SINGLE / 系列 SERIES)
     * @apiParam {String} [name] 关键字，搜索服务名称
     * @apiParam {string} [category_ids] 分类 id  (ps 多选以英文逗号分割)
     * @apiParam {string=category} [include]  服务的分类
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *   {
     *       "data": [
     *           {
     *               "id": 113,
     *               "user_id": 354,
     *               "category_id": 0,
     *               "custom_category": "这是前端传的字符串",
     *               "type": "CUSTOM",
     *               "name": "服务名称",
     *               "content": "",
     *               "en_content": "",
     *               "attachment_type": "",
     *               "cover_picture_url": "http://dev.defara.com/images/users/user.jpg",
     *               "duration": 7,
     *               "description": "这是服务描述",
     *               "need_delivery": null,
     *               "is_free": null,
     *               "price": "1000.00",
     *               "status": "ACTIVE",
     *               "visit_count": 0,
     *               "updated_at": "2016-03-28 03:57:19",
     *               "created_at": "2016-03-28 03:57:19",
     *               "category_tag": "SERIES",
     *               "user": {
     *                   "data": {
     *                       "id": 354,
     *                       "type": "DESIGNER",
     *                       "cellphone": "jinkim",
     *                       "email": "test@qq.com",
     *                       "avatar": null,
     *                       "first_name": "te",
     *                       "last_name": "st",
     *                       "gender": "MALE",
     *                       "is_email_verified": 0,
     *                       "is_cellphone_verified": 0,
     *                       "created_at": "2016-03-14 03:30:25",
     *                       "updated_at": "2016-03-23 08:51:38",
     *                       "amount": "0.00",
     *                       "status": "ACTIVE",
     *                       "nickname": "test",
     *                       "birthday": "2016-03-24 15:51:41",
     *                       "privilege_amount": 0,
     *                       "position": "",
     *                       "is_verify": 1,
     *                       "fullname": "te st"
     *                   }
     *               },
     *               "category": null
     *           }
     *       "meta": {
     *           "pagination": {
     *               "total": 10,
     *               "count": 3,
     *               "per_page": 3,
     *               "current_page": 1,
     *               "total_pages": 4,
     *               "links": {
     *                   "next": "http://dev.defara.com/api/user/services?page=2"
     *               }
     *           }
     *       }
     *   }
     */
    public function userIndex(UserIndexRequest $request)
    {
        $user = \Auth::user();

        $this->authorize('userIndex', Service::class);

        $services = $user->services();
        $services = $this->searchService($services, $request);

        $services = $services->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($services, new ServiceTransformer());
    }

    /**
     * @apiGroup designer
     * @apiDescription 删除当前设计师的服务
     *
     * @api {delete} /services/{id} 删除当前设计师的服务
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {integer} id 服务id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return $this->response->errorNotFound();
        }

        $this->authorize('destroy', $service);

        $service->delete();

        return $this->response->noContent();
    }

    /**
     * @apiGroup designer
     * @apiDescription 修改当前设计师服务
     *
     * @api {put} /services/{id} 修改当前设计师服务
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} [name] 服务名称
     * @apiParam {String} [category_tag] 单品(SINGLE)/系列(SERIES)
     * @apiParam {String} [status] 上架(ACTIVE)/下架(INACTIVE)
     * @apiParam {String} [category_id]  分类id(单品)
     * @apiParam {String} [price]        价格
     * @apiParam {String} [duration]     所需时间
     * @apiParam {String} [cover_picture_url] 封面图片地址 tag =cover
     * @apiParam {Array} [attachments]    详情图 tag= detail
     * @apiParam {String} [description]       服务描述
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function update($id, UpdateRequest $request)
    {
        $service = Service::find($id);

        if (!$service) {
            $this->response->errorNotFound();
        }

        // 直接 $this->authorize($service);也行, 默认是根据方法名，为了写统一
        $this->authorize('update', $service);

        $service->fill(array_filter($request->input()));
        $service->category_id = $request->get('category_tag') == 'SINGLE' ? $request->get('category_id') : 0;
        $service->category_tag = strtoupper($request->get('category_tag'));
        if ($status = $request->get('status')) {
            $service->status = strtoupper($status);
        }

        $result = $service->update();

        if (!$result) {
            $this->response->errorInternal();
        }

        if ($attachments = $request->get('attachments')) {
            $service->updateAttachment($attachments, 'detail');
        }

        return $this->response->noContent();
    }

    private function searchService($services, $request)
    {
        // 按名称搜索
        if ($name = $request->get('keyword')) {
            $services->where(function ($query) use ($name) {
                $query->where('name', 'like', '%'.$name.'%')
                    ->orWhere('en_name', 'like', '%'.$name.'%');
            });
        }

        if ($categoryIds = $request->get('category_ids')) {
            $categoryIds = array_filter(array_map('intval', explode(',', $categoryIds)));

            if ($categoryIds) {
                $childrenIds = Category::whereIn('parent_id', $categoryIds)->lists('id')->toArray();
                $categoryIds = array_merge($categoryIds, $childrenIds);
                $services->whereIn('category_id', $categoryIds);
            }
        }

        $categoryTag = $request->get('category_tag');
        $categoryTag = $categoryTag ? strtoupper($categoryTag) : null;
        if (in_array($categoryTag, ['SERIES', 'SINGLE'])) {
            $services->where('category_tag', $categoryTag);
        }

        if ($minPrice = (int) $request->get('min_price')) {
            $services->where('price', '>=', $minPrice);
        }

        if ($maxPrice = (int) $request->get('max_price')) {
            $services->where('price', '<=', $maxPrice);
        }

        //开始时间
        if ($startTime = $request->get('start_time')) {
            $services->where('created_at', '>=', $startTime);
        }

        //结束时间
        if ($endTime = $request->get('end_time')) {
            $services->where('created_at', '<=', $endTime);
        }

        return $services;
    }
}
