<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\WorkTransformer;
use App\Http\Requests\Api\Work\StoreRequest;
use App\Http\Requests\Api\Work\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\Attachment;
use App\Models\DesignerWork;
use App\Models\User;

class WorkController extends BaseController
{
    /**
     * @apiGroup designer
     * @apiDescription 某个设计师的作品列表
     *
     * @api {get} /designers/{id}/works 某个设计师的作品列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [perPage]  可选分页
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *
     * {
     *  "data": [
     *    {
     *      "id": 1,
     *      "user_id": 7,
     *      "title": "JANE纹理真皮尖头高跟凉鞋",
     *      "description": null,
     *       "is_favorite": false,
     *      "cover_picture_url": "http://alpha.defara.com/assets/content/2015/08/14/6fde0681c956931426c2165270499139.jpg",
     *      "status": "ACTIVE",
     *      "created_at": "2015-08-14 10:30:18",
     *      "updated_at": "2015-08-23 02:30:51"
     *    },
     *    {
     *      "id": 76,
     *      "user_id": 7,
     *      "title": "My life",
     *      "description": null,
     *      "cover_picture_url": "http://alpha.defara.com/assets/content/2015/08/14/ac8a8123f84560d067869d008eebd7d6.png",
     *      "status": "ACTIVE",
     *      "created_at": "2015-08-14 14:14:19",
     *      "updated_at": "2015-08-23 02:31:53"
     *    }
     *  ],
     *  "meta": {
     *    "pagination": {
     *      "total": 2,
     *      "count": 2,
     *      "per_page": 6,
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

        $works = $user->works()->active();
        //是否被登陆用户收藏
        if ($loginUser = $this->user()) {
            $works->with(['followedByCurrentUser' => function ($query) use ($loginUser) {
                $query->where('user_favorites.user_id', $loginUser->id);
            }]);
        }

        $works = $works->orderBy('weight', 'desc')
            ->paginate($request->get('per_page'));

        return $this->response->paginator($works, new WorkTransformer());
    }

    /**
     * @apiGroup designer
     * @apiDescription 作品详情
     *
     * @api {get} /works/{id} 作品详情
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} [include]  可引入的关系
     * @apiParam {string} [include.attachment]  作品图片
     * @apiParam {string} [include.more]  more 更多作品，默认为4个
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": {
     *     "id": 258,
     *     "user_id": 104,
     *     "title": "111222333",
     *     "description": "88888",
     *     "cover_picture_url": "http://alpha.pick1.cn/assets/works/15/12/56829fc90be20.jpg",
     *     "status": "ACTIVE",
     *     "created_at": "2015-12-29 07:46:11",
     *     "updated_at": "2015-12-29 14:59:21",
     *     "attachments": {
     *       "data": [
     *         {
     *           "id": 2653,
     *           "attachable_type": "App\\Models\\DesignerWork",
     *           "attachable_id": 258,
     *           "relative_path": "http://alpha.pick1.cn/assets/works/16/01/7-568b32de7f34a.jpg",
     *           "filename": null,
     *           "description": null,
     *           "tag": "works",
     *           "mime_types": null,
     *           "created_at": "2016-01-05 03:05:03",
     *           "updated_at": "2016-03-14 03:45:33",
     *           "deleted_at": null,
     *           "url": "https://defara.s3.amazonaws.com/userdata/assets/works/16/01/7-568b32de7f34a.jpg"
     *         },
     *         {
     *           "id": 2654,
     *           "attachable_type": "App\\Models\\DesignerWork",
     *           "attachable_id": 258,
     *           "relative_path": "http://alpha.pick1.cn/assets/works/16/01/7-568b32de7f8cb.jpg",
     *           "filename": null,
     *           "description": null,
     *           "tag": "works",
     *           "mime_types": null,
     *           "created_at": "2016-01-05 03:05:03",
     *           "updated_at": "2016-03-14 03:45:33",
     *           "deleted_at": null,
     *           "url": "https://defara.s3.amazonaws.com/userdata/assets/works/16/01/7-568b32de7f8cb.jpg"
     *         }
     *       ]
     *     }
     *     "more": {
     *        "data": [
     *          {
     *            "id": 800,
     *            "user_id": 352,
     *            "title": "My Works ",
     *            "description": "My inspiration Sketches",
     *            "cover_picture_url": "https://defara.imgix.net/userdata/assets/showroom/content/work/2016/03/10/68fd6fab6418ac641481b2d6e9fc3c6bc2c96bd9.jpg",
     *            "status": "ACTIVE",
     *            "created_at": "2016-03-10 06:11:19",
     *            "updated_at": "2016-03-17 03:38:30"
     *          },
     *          {
     *            "id": 801,
     *            "user_id": 352,
     *            "title": "Fashion Weeks Works",
     *            "description": "My Design works for fashion weeks",
     *            "cover_picture_url": "https://defara.imgix.net/userdata/assets/showroom/content/work/2016/03/10/931a6486dc1259d816f2f472a63773f137dead3c.jpg",
     *            "status": "ACTIVE",
     *            "created_at": "2016-03-10 06:24:17",
     *            "updated_at": "2016-03-17 03:38:30"
     *          }
     *        ]
     *      }
     *   }
     * }
     */
    public function show($id, Request $request)
    {
        $work = DesignerWork::find($id);

        if (!$work) {
            return $this->response->errorNotFound();
        }

        //是否被登陆用户收藏
        if ($loginUser = $this->user()) {
            $followers = $work->followers()->where('user_favorites.user_id', $loginUser->id)->first();
            $work->setRelation('followedByCurrentUser', $followers);
        }

        return $this->response->item($work, new WorkTransformer($loginUser));
    }

    /**
     * @apiGroup designer
     * @apiDescription 当前用户的作品列表
     *
     * @api {get} /user/works 当前用户的作品列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} [perPage]  可选分页
     * @apiParam {String} [keyword] 关键字，搜索名称
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": [
     *      {
     *          "id": 35,
     *          "user_id": 49,
     *          "title": "My work",
     *          "description": "用独有的视角和审美为现代女性带来全新的体验和感受.强调线条与结构的美学,在质量与美感并重的同时,也将在传统与现代时装的创新性与实用性中找到更好的平衡.",
     *          "cover_picture_url": "https://defara.imgix.net/userdata/assets/content/2015/09/01/c5ea15cb3fbb320ac642e768239bc310.jpg",
     *          "status": "ACTIVE",
     *          "created_at": "2015-09-01 07:27:45",
     *          "updated_at": "2016-03-16 07:56:07"
     *      },
     *      {
     *          "id": 36,
     *          "user_id": 49,
     *          "title": "My life",
     *          "description": "我想，我就是为设计而生的，在我17、18岁的时候，我就和我的家人说，我想要成为一个设计师，用尽我的一生去做设计。从小，我的父母总是鼓励我去玩，现在，我依旧在玩。我会在每个重要会议之前玩游戏，以确保我的头脑保持灵光。 生活和工作对于我来说是合一的，平时，我会和我的家人谈论设计，我的孩子们给了我很多灵感启发，我展示厅的每一件样品，我都精心设计。",
     *          "cover_picture_url": "https://defara.imgix.net/userdata/assets/content/2015/09/01/f45664113d735f2b1b569e281ff42071.jpg",
     *          "status": "ACTIVE",
     *          "created_at": "2015-09-01 07:28:34",
     *          "updated_at": "2016-03-16 07:56:07"
     *      },
     *      {
     *          "id": 37,
     *          "user_id": 49,
     *          "title": "My idea",
     *          "description": "所谓时尚，是一种风格，也是一种生活态度，不盲从，不跟风，浅吟低唱，用品质与细节来诠释时尚的真谛。",
     *          "cover_picture_url": "https://defara.imgix.net/userdata/assets/content/2015/09/01/61d7ae0ac0c2a5e9c9b54acedba11c1a.jpg",
     *          "status": "ACTIVE",
     *          "created_at": "2015-09-01 07:29:51",
     *          "updated_at": "2016-03-16 07:56:07"
     *      }
     *      ],
     *  "meta": {
     *      "pagination": {
     *          "total": 3,
     *          "count": 3,
     *          "per_page": 20,
     *          "current_page": 1,
     *          "total_pages": 1,
     *          "links": []
     *      }
     *  }
     * }
     */
    public function userIndex(Request $request)
    {
        $user = \Auth::User();

        if ($user->type != 'DESIGNER') {
            return $this->response->errorForbidden();
        }

        $works = $user->works();

        if ($keyword = $request->get('keyword')) {
            $works->where(function ($query) use ($keyword) {
                $query->where('title', 'like', '%'.$keyword.'%')
                    ->orWhere('en_title', 'like', '%'.$keyword.'%')
                    ->orWhere('id', 'like', '%'.$keyword.'%');
            });
        }
        $works = $works->orderBy('weight', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($works, new WorkTransformer());
    }

    /**
     * @apiGroup designer
     * @apiDescription 添加作品
     *
     * @api {post} /works 添加作品
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} title 标题
     * @apiParam {String} description 描述
     * @apiParam {String} cover_picture_url 图片地址 tag =cover
     * @apiParam {String} [attachments] 详情图 tag =detail
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 created
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::User();

        $weight = DesignerWork::where('user_id', $user->id)->max('weight');

        $attachments = $request->get('attachments');
        //构造数据
        $work = new DesignerWork();
        $work->fill($request->all());
        $work->weight = $weight + 1;
        $work->status = 'ACTIVE';

        // 处理
        if ($attachments && $attachmentIds = collect($attachments)->pluck('id')) {
            $work->extra = ['attachment_ids' => $attachmentIds];
        }

        $work->user()->associate($user)->save();

        //详情图
        if ($attachments) {
            $work->updateAttachment($attachments, 'detail');
        }

        return $this->response->created();
    }

    /**
     * @apiGroup designer
     * @apiDescription 修改作品
     *
     * @api {put} /works/{id} 修改作品
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {integer} id 作品id
     * @apiParam {String} [title] 标题
     * @apiParam {String} [description] 描述
     * @apiParam {String='ACTIVE','INACTIVE','active','inactive'} [status] 状态
     * @apiParam {String} [cover_picture_url] 图片地址 tag=cover
     * @apiParam {String} [attachments] 详情图 tag =detail
     * @apiParam {String='UP','DOWN'} [orderby] 信息记录排序,状态
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::User();

        $work = $user->works()->find($id);

        if (!$work) {
            return $this->response->errorNotFound();
        }

        //构造数据
        $attachments = $request->get('attachments');
        $work->fill($request->input());

        if ($status = $request->get('status')) {
            $work->status = strtoupper($status);
        }

        if ($attachments && $attachmentIds = collect($attachments)->pluck('id')) {
            $extra = $work->extra ?: [];
            $extra['attachment_ids'] = $attachmentIds;
            $work->extra = $extra;
        }

        $work->save();

        //详情图
        if ($attachments) {
            $work->updateAttachment($attachments, 'detail');
        }
        //排序
        if ($orderby = $request->get('orderby')) {
            $weight = $work->weight;
            $symbol = ['UP' => '>', 'DOWN' => '<'];
            $operate = ['UP' => 'asc', 'DOWN' => 'desc'];

            $operateWork = $user->works()
                ->where('weight', $symbol[$orderby], $work->weight)
                ->orderBy('weight', $operate[$orderby])
                ->first();

            if ($operateWork) {
                $work->weight = $operateWork->weight;
                $operateWork->weight = $weight;
                $work->save();
                $operateWork->save();
            }
        }

        return $this->response->noContent();
    }

    /**
     * @apiGroup designer
     * @apiDescription 删除作品
     *
     * @api {delete} /works/{id} 删除作品
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {integer} id 作品id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function destroy($id)
    {
        $user = \Auth::User();
        if ($user->type != 'DESIGNER') {
            return $this->response->errorForbidden();
        }

        $work = $user->works()->find($id);

        if (!$work) {
            return $this->response->errorNotFound();
        }

        $work->delete();

        return $this->response->noContent();
    }
}
