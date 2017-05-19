<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\FavoriteTransformer;
use App\Http\Requests\Api\Favorite\StoreRequest;
use App\Http\Requests\Api\Favorite\IndexRequest;
use App\Http\Requests\Api\Favorite\DestoryRequest;
use App\Models\Service;
use App\Models\User;
use App\Models\UserFavorite;

class FavoriteController extends BaseController
{
    /**
     * @apiGroup Favorite
     * @apiDescription  当前用户收藏列表
     *
     * @api {get} /user/favorites 当前用户收藏列表
     * @apiParam {string = 'designer','maker','product','work','service'} type 收藏类型   //按分类显示，没有type默认显示全部收藏
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} [keyword]  搜索
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": [
     *      {
     *          "id": 65,
     *          "user_id": 49,
     *          "name": null,
     *          "created_at": "2016-03-21 07:41:08",
     *          "updated_at": "2016-03-21 07:41:08",
     *          "status": "ACTIVE",
     *          "tag": "WORK",
     *          "work": {
     *              "data": {
     *                  "id": 35,
     *                  "user_id": 49,
     *                  "title": "My work",
     *                  "description": "用独有的视角和审美为现代女性带来全新的体验和感受.强调线条与结构的美学,在质量与美感并重的同时,也将在传统与现代时装的创新性与实用性中找到更好的平衡.",
     *                  "cover_picture_url": "https://defara.imgix
     *                  .net/userdata/assets/content/2015/09/01/c5ea15cb3fbb320ac642e768239bc310.jpg",
     *                  "status": "ACTIVE",
     *                  "created_at": "2015-09-01 07:27:45",
     *                  "updated_at": "2016-03-16 07:56:07"
     *              }
     *          }
     *      },
     *      {
     *          "id": 109,
     *          "user_id": 49,
     *          "name": null,
     *          "created_at": "2016-03-21 07:51:43",
     *          "updated_at": "2016-03-21 07:51:43",
     *          "status": "ACTIVE",
     *          "tag": "WORK",
     *          "work": {
     *              "data": {
     *                  "id": 132,
     *                  "user_id": 14,
     *                  "title": "My idea",
     *                  "description": "感受色彩与造型，聆听布料在运动和摩擦时所发出的声音",
     *                  "cover_picture_url": "https://defara.imgix
     *                  .net/userdata/assets/content/2015/09/02/3f520f7f4e5d5823aac1f5137bc839ff.jpg",
     *                  "status": "ACTIVE",
     *                  "created_at": "2015-09-02 07:08:21",
     *                  "updated_at": "2016-03-16 07:56:07"
     *              }
     *          }
     *      },
     *      {
     *          "id": 110,
     *          "user_id": 49,
     *          "name": null,
     *          "created_at": "2016-03-21 07:52:22",
     *          "updated_at": "2016-03-21 07:52:22",
     *          "status": "ACTIVE",
     *          "tag": "SERVICE",
     *          "service": {
     *              "data": {
     *                  "id": 31,
     *                  "user_id": 105,
     *                  "category_id": 32,
     *                  "custom_category": "Dress",
     *                  "type": "CUSTOM",
     *                  "name": "Designer",
     *                  "attachment_type": "",
     *                  "cover_picture_url": "https://defara.imgix
     *                  .net/userdata/assets/service/2015/10/16/fdc5b784b980c9d086cf84e0e5c2fb432c3f2d32.jpg",
     *                  "duration": null,
     *                  "description": "Designing ",
     *                  "need_delivery": 0,
     *                  "is_free": 0,
     *                  "price": "0.00",
     *                  "status": "ACTIVE",
     *                  "visit_count": 0,
     *                  "updated_at": "2016-03-16 07:56:07",
     *                  "created_at": "2015-10-16 14:51:34"
     *              }
     *          }
     *      },
     *      {
     *          "id": 111,
     *          "user_id": 49,
     *          "name": null,
     *          "created_at": "2016-03-21 07:53:12",
     *          "updated_at": "2016-03-21 07:53:12",
     *          "status": "ACTIVE",
     *          "tag": "DESIGNER",
     *          "designer": {
     *              "data": {
     *                  "id": 31,
     *                  "type": "DESIGNER",
     *                  "cellphone": "",
     *                  "email": "490554191@qq.com",
     *                  "avatar": null,
     *                  "first_name": "",
     *                  "last_name": "",
     *                  "gender": "",
     *                  "is_email_verified": 1,
     *                  "is_cellphone_verified": 0,
     *                  "created_at": "2015-08-26 04:56:11",
     *                  "updated_at": "2016-03-16 07:55:06",
     *                  "amount": "0.00",
     *                  "status": "INACTIVE",
     *                  "nickname": "",
     *                  "birthday": "0000-00-00 00:00:00",
     *                  "privilege_amount": 0,
     *                  "position": "",
     *                  "is_verify": 1
     *              }
     *          }
     *      }
     *      ],
     *      "meta": {
     *          "pagination": {
     *              "total": 4,
     *              "count": 4,
     *              "per_page": 20,
     *              "current_page": 1,
     *              "total_pages": 1,
     *              "links": []
     *          }
     *      }
     * }
     */
    public function index(IndexRequest $request)
    {
        $user = \Auth::User();

        if (($user->type != 'DEALER' && $user->is_verify == 0)) {
            return $this->response->errorForbidden();
        }

        $favorites = $user->following();

        if ($type = $request->get('type')) {
            $favorites->where('tag', $type);
        }

        //搜索用户TODO  所有类型都应该能搜索
        if ($keyword = $request->get('keyword')) {
            $favorites->leftJoin('factories', 'factories.user_id', '=', 'user_favorites.user_id');
            $favorites->where(function ($query) use ($keyword) {
                $query->where('factories.name', 'like', '%'.$keyword.'%')
                    ->orWhere('factories.en_name', 'like', '%'.$keyword.'%');
            });

            $favorites->select('user_favorites.*');
        }

        $favorites = $favorites->where('status', 'ACTIVE')->paginate($request->get('per_page'));

        return $this->response()->paginator($favorites, new FavoriteTransformer());
    }

    /**
     * @apiGroup Favorite
     * @apiDescription 添加收藏
     *
     * @api {post} /favorites 添加收藏
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String = 'designer','maker','service','work','DESIGNER','MAKER','SERVICE','WORK'} type 收藏用户类型   //目前没有product
     * @apiParam {Integer} type_id 收藏用户id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 created
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::User();

        if (($user->is_verify == 0 && in_array($user->type, ['MAKER', 'DESIGNER']))) {
            return $this->response->errorForbidden();
        }

        $type = strtolower($request->get('type'));
        if ($type == $user->type) {
            return $this->response->errorForbidden();
        }
        $type_id = $request->get('type_id');
        $object = null;
        switch ($type) {
            case 'designer':
                $object = User::where('type', 'DESIGNER')->find($type_id);
                break;
            case 'service':
                $object = Service::find($type_id);
                break;
            case 'work':
                $object = DesignerWork::find($type_id);
                break;
            case 'maker':
                $object = User::where('type', 'MAKER')->find($type_id);
                break;
        }

        if (!$object) {
            return $this->response->errorNotFound();
        }

        $favorite = $object->followers()->where('user_id', $user->id)->exists();

        if ($favorite) {
            return $this->response->errorForbidden();
        }

        $userFavorite = new UserFavorite();

        $userFavorite->user()->associate($user);
        $userFavorite->tag = $type;

        $userFavorite->favorable()->associate($object)->save();

        return $this->response->created();
    }

    /**
     * @apiGroup Favorite
     * @apiDescription 取消收藏
     *
     * @api {delete} /favorites/{id} 取消收藏
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {int} id 收藏id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function destroy($id)
    {
        $user = \Auth::User();

        if ($user->is_verify == 0) {
            return $this->response->errorForbidden();
        }

        $favorite = $user->following()->find($id);

        if (!$favorite) {
            return $this->response->errorNotFound();
        }

        $favorite->delete();

        return $this->response->noContent();
    }
    /**
     * @apiGroup Favorite
     * @apiDescription 取消列表详情收藏
     *
     * @api {delete} /favorites 取消列表详情收藏
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String = 'designer','maker','service','work','DESIGNER','MAKER','SERVICE','WORK'} type 收藏用户类型   //目前没有product
     * @apiParam {Integer} type_id 收藏用户id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function favoriteDestroy(DestoryRequest $request)
    {
        $user = \Auth::User();

        if ($user->is_verify == 0) {
            return $this->response->errorForbidden();
        }

        $favorite = $user->following()
            ->where('user_favorites.tag', strtoupper($request->get('type')))
            ->where('user_favorites.favorable_id', $request->get('type_id'))
            ->first();

        if (!$favorite) {
            return $this->response->errorNotFound();
        }

        $favorite->delete();

        return $this->response->noContent();
    }
}
