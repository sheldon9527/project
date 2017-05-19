<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use App\Models\Category;
use App\Models\Attachment;
use Illuminate\Http\Request;
use App\Models\InquiryService;
use App\Models\ServiceResult;
use App\Transformers\AttachmentTransformer;
use App\Http\Controllers\Api\BaseController;
use App\Transformers\InquiryServiceTransformer;
use App\Http\Requests\Api\InquiryService\StoreRequest;
use App\Http\Requests\Api\InquiryService\UpdateRequest;

class InquiryServiceController extends BaseController
{
    /**
     * @apiGroup designer
     * @apiDescription 某个设计师的分类服务作品
     *
     * @api {get} /designers/{id}/service/works 某个设计师的分类服务作品
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [category_id] 分类id
     * @apiParam {string} [page]  可选分页
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 1028,
     *      "user_id": 355,
     *      "relative_path": "https://defara.imgix.net/userdata/assets/recommend/content/2015/12/10/e9c2c04bb735d0a1bb7c93c493caab0b.jpg",
     *      "filename": null,
     *      "description": null,
     *      "tag": "detail",
     *      "mime_types": null,
     *      "created_at": "2015-12-10 11:25:38",
     *      "updated_at": "2016-04-18 13:49:57",
     *      "url": "https://defara.imgix.net/userdata/assets/recommend/content/2015/12/10/e9c2c04bb735d0a1bb7c93c493caab0b.jpg"
     *    },
     *    {
     *      "id": 1031,
     *      "user_id": 355,
     *      "relative_path": "https://defara.imgix.net/userdata/assets/recommend/content/2016/02/02/2f700b9031796eca5874afd4cd25e0f9.jpg",
     *      "filename": null,
     *      "description": null,
     *      "tag": "detail",
     *      "mime_types": null,
     *      "created_at": "2015-12-10 11:32:16",
     *      "updated_at": "2016-04-18 13:49:57",
     *      "url": "https://defara.imgix.net/userdata/assets/recommend/content/2016/02/02/2f700b9031796eca5874afd4cd25e0f9.jpg"
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
    public function designerIndex($id, Request $request)
    {
        $user = User::find($id);

        if ($user->type != 'DESIGNER') {
            return $this->response->errorNotFound();
        }

        $inquiryServiceWorks = Attachment::where('attachments.user_id', $user->id)
            ->leftJoin('inquiry_services', 'inquiry_services.id', '=', 'attachments.attachable_id')
            ->where('inquiry_services.status', '=', 'ACTIVE')
            ->where('attachable_type', \App\Models\InquiryService::class);

        $categoryId = (int) $request->get('category_id');

        if ($categoryId) {
            $inquiryService = $user->inquiryServices()->where('category_id', $categoryId)->first();

            if (!$inquiryService) {
                return $this->response->errorNotFound();
            }

            $inquiryServiceWorks->where('attachable_type', get_class($inquiryService))->where('attachable_id', $inquiryService->id);
        }

        $inquiryServiceWorks = $inquiryServiceWorks
            ->orderBy('attachments.weight', 'DESC')
            ->paginate($request->get('per_page'));

        return $this->response->paginator($inquiryServiceWorks, new AttachmentTransformer());
    }

    /**
     * @apiGroup inquiryService
     * @apiDescription 添加设计服务
     *
     * @api {post} /inquiry_service 添加设计服务
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Array} category_ids 服务的分类
     * @apiParam {Number} min_price 服务的价格
     * @apiParam {Array} service_results 服务的设计资料
     * @apiParam {Array} works 服务的作品
     * @apiParam {Integer=0，1} status 是否上架， 1 为上架，0 为不上架
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::user();

        $returnInfo = $this->_validateCategory($request, $user);

        if (isset($returnInfo['errors'])) {
            return response()->json($returnInfo['errors'], 400);
        }

        //验证多个作品时，某个作品为空的情况
        $validateResult = $this->_validateAttachment($request);

        if ($validateResult['validateMark']) {
            return response()->json(['errors' => ['rootCategory' => trans('error.service.works')]], 400);
        }

        $service = new InquiryService();
        $service->user_id = $user->id;
        $service->min_price = $request->get('min_price');
        $service->category_id = $returnInfo['rootCategoryId'];
        $service->status = $request->get('status');
        $service->save();

        // 修改附件
        $service->updateAttachment($validateResult['works']);

        $categoryIds = $request->get('category_ids');
        $user->categories()->attach($categoryIds);
        $service->categories()->attach($categoryIds);
        $service->results()->attach($request->get('service_results'));

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiryService
     * @apiDescription 设计服务列表
     *
     * @api {get} /inquiry_service 设计服务列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} [include] 可引入的关系
     * @apiParam {string} [include.categories] 服务的分类
     * @apiParam {string} [include.results] 服务的设计资料
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200
     *
     *   {
     *       "data": [
     *           {
     *               "id": 16,
     *               "category_id": 1,
     *               "cover_picture_url": "/",
     *               "min_price": "12.10",
     *               "category_name": "女装",
     *               "categories": {
     *                   "data": [
     *                       {
     *                           "id": 2,
     *                           "parent_id": 1,
     *                           "name": "T恤&衬衣",
     *                           "icon_url": ""
     *                       },
     *                       {
     *                           "id": 3,
     *                           "parent_id": 1,
     *                           "name": "外套/夹克",
     *                           "icon_url": ""
     *                       }
     *                   ]
     *               },
     *               "results": {
     *                   "data": [
     *                       {
     *                           "id": 1,
     *                           "name": "草图",
     *                           "key": "sketch"
     *                       },
     *                       {
     *                           "id": 2,
     *                           "name": "工艺单",
     *                           "key": "teckPack"
     *                       }
     *                   ]
     *               }
     *           },
     *           {
     *               "id": 11,
     *               "category_id": 1,
     *               "cover_picture_url": "/",
     *               "min_price": "12.10",
     *               "category_name": "女装",
     *               "categories": {
     *                   "data": [
     *                       {
     *                           "id": 2,
     *                           "parent_id": 1,
     *                           "name": "T恤&衬衣",
     *                           "icon_url": ""
     *                       },
     *                       {
     *                           "id": 3,
     *                           "parent_id": 1,
     *                           "name": "外套/夹克",
     *                           "icon_url": ""
     *                       }
     *                   ]
     *               },
     *               "results": {
     *                   "data": [
     *                       {
     *                           "id": 1,
     *                           "name": "草图",
     *                           "key": "sketch"
     *                       },
     *                       {
     *                           "id": 2,
     *                           "name": "工艺单",
     *                           "key": "teckPack"
     *                       }
     *                   ]
     *               }
     *           },
     *           {
     *               "id": 10,
     *               "category_id": 1,
     *               "cover_picture_url": "/",
     *               "min_price": "12.10",
     *               "category_name": "女装",
     *               "categories": {
     *                   "data": []
     *               },
     *               "results": {
     *                   "data": []
     *               }
     *           }
     *       ],
     *       "meta": {
     *           "pagination": {
     *               "total": 12,
     *               "count": 12,
     *               "per_page": 20,
     *               "current_page": 1,
     *               "total_pages": 1,
     *               "links": {
     *               "next": "http://dev.defara.com/api/inquiry_service?page=2"
     *               }
     *           }
     *       }
     *   }
     */
    public function index()
    {
        $user = \Auth::user();
        $services = $user->inquiryServices()->orderBy('created_at', 'desc')->paginate();

        return $this->response->paginator($services, new InquiryServiceTransformer());
    }

    /**
     * @apiGroup inquiryService
     * @apiDescription 设计服务详情
     *
     * @api {get} /inquiry_service/{id} 设计服务详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} [include]
     * @apiParam {String} [include.categories]   设计服务分类
     * @apiParam {String} [include.results]    设计资料
     * @apiParam {String} [include.attachments]    设计服务的附件
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *   {
     *       "data": {
     *       "id": 17,
     *       "category_id": 39,
     *       "cover_picture_url": "/",
     *       "min_price": "12.10",
     *       "category_name": "鞋履",
     *       "categories": {
     *           "data": [
     *           {
     *               "id": 40,
     *             "parent_id": 39,
     *             "name": "女士靴子",
     *             "icon_url": ""
     *           },
     *           {
     *               "id": 41,
     *             "parent_id": 39,
     *             "name": "高跟鞋",
     *             "icon_url": ""
     *           }
     *         ]
     *       },
     *       "results": {
     *           "data": [
     *           {
     *               "id": 1,
     *             "name": "草图",
     *             "key": "sketch"
     *           },
     *           {
     *               "id": 2,
     *             "name": "工艺单",
     *             "key": "teckPack"
     *           }
     *         ]
     *       }
     *     }
     *     "meta": {
     *       "service_result": [
     *         {
     *           "id": 1,
     *           "name": "草图",
     *           "key": "sketch"
     *         },
     *         {
     *           "id": 2,
     *           "name": "工艺单",
     *           "key": "teckPack"
     *         },
     *         {
     *           "id": 3,
     *           "name": "面料实物",
     *           "key": "materialSample"
     *         },
     *         {
     *           "id": 4,
     *           "name": "服饰原样",
     *           "key": "originalSample"
     *         }
     *       ]
     *     }
     *   }
     */
    public function show($id)
    {
        $service = InquiryService::find($id);
        if (!$service) {
            $this->response->errorNotFound();
        }

        $results = ServiceResult::all();

        return $this->item($service, new InquiryServiceTransformer())
            ->addMeta('service_results', $results);
    }

    /**
     * @apiGroup inquiryService
     * @apiDescription 设计服务修改
     *
     * @api {put} /inquiry_service/{id} 设计服务修改
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Array} category_ids 服务的分类
     * @apiParam {Number} min_price 服务的价格
     * @apiParam {Array} service_results 服务的设计资料
     * @apiParam {Array} works 服务的作品
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 OK
     */
    public function update($id, UpdateRequest $request)
    {
        $service = InquiryService::find($id);
        if (!$service) {
            return response()->isNotFound();
        }

        if ($active = $request->get('status')) {
            $service->status = strtoupper($active);
            $service->save();

            return $this->response->noContent();
        }

        $user = $service->user;

        $returnInfo = $this->_validateCategory($request, $user, $id);

        if (isset($returnInfo['errors'])) {
            return response()->json($returnInfo['errors'], 400);
        }

        $validateResult = $this->_validateAttachment($request);

        if ($validateResult['validateMark']) {
            return response()->json(['errors' => ['rootCategory' => trans('error.service.works')]], 400);
        }

        // 修改附件
        $service->updateAttachment($validateResult['works']);

        $oldCategory = $service->categories->lists('id')->toArray();
        $user->categories()->detach($oldCategory);
        $service->categories()->detach();

        if ($categoryIds = $request->get('category_ids')) {
            $user->categories()->attach($categoryIds);
            $service->categories()->attach($categoryIds);
        }

        $service->results()->detach();
        if ($results = $request->get('service_results')) {
            $service->results()->attach($results);
        }

        $service->category_id = $returnInfo['rootCategoryId'];
        $service->min_price = $request->get('min_price');
        $service->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiryService
     * @apiDescription 设计服务删除
     *
     * @api {delete} /inquiry_service/{id} 设计服务删除
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 OK
     */
    public function destroy($id)
    {
        $service = InquiryService::find($id);
        if (!$service) {
            $this->response->errorNotFound();
        }

        $user = $service->user;

        $oldCategory = $service->categories->lists('id')->toArray();

        $user->categories()->detach($oldCategory);
        $service->categories()->detach();

        $service->results()->detach();

        $service->delete();

        return $this->response->noContent();
    }

    public function _validateCategory($request, $user, $id = '')
    {
        $returnInfo = [];
        $categoryIds = $request->get('category_ids');
        $categoryIds = $categoryIds ? array_filter(array_map('intval', $categoryIds)) : [];
        if (!$categoryIds) {
            $returnInfo['errors'] = ['errors' => ['rootCategory' => trans('error.service.category_ids')]];

            return $returnInfo;
        }

        $rootCategories = Category::whereIn('id', $categoryIds)->groupBy('parent_id')->get();
        // 判断主分类个数
        if ($rootCategories->count() > 1) {
            $returnInfo['errors'] = ['errors' => ['rootCategory' => trans('error.service.rootCategory')]];

            return $returnInfo;
        }

        // 判断分类服务是否重复
        $rootCategory = $rootCategories->first();
        $returnInfo['rootCategoryId'] = $rootCategory->parent_id;

        $queryExist = InquiryService::where('user_id', $user->id)
            ->where('category_id', $rootCategory->parent_id);
        if ($id) {
            $queryExist->where('id', '!=', $id);
        }
        $result = $queryExist->exists();
        if ($result) {
            $returnInfo['errors'] = ['errors' => ['rootCategory' => trans('error.service.category_exists')]];

            return $returnInfo;
        }

        $childrenCategories = Category::where('parent_id', $rootCategory->parent_id)->whereIn('id', $categoryIds)->get();
        // 子分类超过5个
        if ($childrenCategories->count() > 5) {
            $returnInfo['errors'] = ['errors' => ['rootCategory' => trans('error.service.childrenCategory')]];

            return $returnInfo;
        }

        return $returnInfo;
    }

    public function _validateAttachment($request)
    {
        $allowed = ['id'];
        $works = $request->get('works');

        $returnInfo = [];
        //验证多个作品时，某个作品为空的情况
        $validateMark = 0;
        array_walk($works, function (&$works) use ($allowed, &$validateMark) {
            if (!array_filter($works)) {
                $validateMark = 1;
            }
            $works = array_intersect_key($works, array_flip($allowed));
        });

        $returnInfo['works'] = $works;
        $returnInfo['validateMark'] = $validateMark;

        return $returnInfo;
    }
}
