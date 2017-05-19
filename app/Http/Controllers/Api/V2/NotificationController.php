<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\NotificationTransformer;
use App\Http\Requests\Api\Notification\ReadRequest;
use App\Http\Requests\Api\Notification\UpdateRequest;
use App\Http\Requests\Api\Notification\DestoryRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends BaseController
{
    /**
     * @apiGroup Notification
     * @apiDescription  当前消息列表
     *
     * @api {get} /user/notifications 当前消息列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} [keyword]  搜索
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 2401,
     *      "user_id": 34,
     *      "content": "抱歉，DEFARA认为你的提现申请不符合平台提现规则，因此未予通过。具体原因如下：:到返回高速的风格服饰的公司的广泛受到感受对方感受到",
     *      "title": "提现失败",
     *      "is_read": 0,
     *      "created_at": "2016-05-10 06:33:23",
     *      "updated_at": "2016-05-10 06:33:23",
     *      "type": "withdraw_order",
     *      "type_id": 1,
     *      "label": "refuse"
     *    },
     *    {
     *      "id": 2400,
     *      "user_id": 34,
     *      "content": "抱歉，DEFARA认为你的提现申请不符合平台提现规则，因此未予通过。具体原因如下：:content",
     *      "title": "提现失败",
     *      "is_read": 0,
     *      "created_at": "2016-05-10 06:29:42",
     *      "updated_at": "2016-05-10 06:29:42",
     *      "type": "withdraw_order",
     *      "type_id": 1
     *    }
     *  ],
     *  "meta": {
     *    "unlook_count": 5,
     *    "pagination": {
     *      "total": 5,
     *      "count": 2,
     *      "per_page": 2,
     *      "current_page": 1,
     *      "total_pages": 3,
     *      "links": {
     *        "next": "http://xiaodong.dev/api/user/notifications?page=2"
     *      }
     *    }
     *  }
     *}
     */
    public function index(Request $request)
    {
        $user = \Auth::User();

        $notifications = $user->notifications();

        //搜索
        if ($keyword = $request->get('keyword')) {
            $notifications->where(function ($query) use ($keyword) {
                $query->orwhere('id', $keyword)
                    ->orWhere('title', 'like', '%'.$keyword.'%')
                    ->orWhere('en_title', 'like', '%'.$keyword.'%')
                    ->orWhere('content', 'like', '%'.$keyword.'%')
                    ->orWhere('en_content', 'like', '%'.$keyword.'%');
            });
        }

        $notifications = $notifications->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));
        $count = $user->notifications()->where('is_read', 0)->count();

        return $this->response()->paginator($notifications, new NotificationTransformer())->addMeta('unlook_count', $count);
    }
    /**
     * @apiGroup Notification
     * @apiDescription  消息详情
     *
     * @api {get} /notifications/{id} 消息详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *     "data": {
     *     "id": 2406,
     *     "user_id": 382,
     *     "content": "我们已收到你的认证申请，会在7天内完成表单信息审核。审核通过后，你将拥有个人主页，设计师账户将被激活；若审核不通过，我们也会及时告知审核结果，你仍然可以完善信息，重新申请认证。请耐心等待，谢谢！",
     *     "title": "认证信息审核中",
     *     "is_read": 0,
     *     "created_at": "2016-05-10 09:23:24",
     *     "updated_at": "2016-05-10 09:23:24",
     *     "type": "userAuthentication",
     *     "type_id": 4
     *   }
     * }
     */
    public function show($id, Request $request)
    {
        $user = \Auth::User();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($notification, new NotificationTransformer());
    }
    /**
     * @apiGroup Notification
     * @apiDescription  批量消息阅读
     *
     * @api {post} /notifications/read 批量消息阅读
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Array} [ids] 消息ID,如果type不传，ids不能为空；
     * @apiParam {String='all','time'} [type] 消息阅读
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     */
    public function read(ReadRequest $request)
    {
        $user = \Auth::User();

        $notifications = $user->notifications()->where('is_read', 0);

        if ($ids = $request->get('ids')) {
            $notifications->whereIn('id', array_filter($ids));
        }

        if ($request->get('time') == 'time') {
            $notifications->where('created_at', '<', Carbon::now());
        }

        $notifications->update(['is_read' => 1]);

        return $this->response->noContent();
    }

    /**
     * @apiGroup Notification
     * @apiDescription  消息一条阅读
     *
     * @api {put} /notifications/{id} 消息一条阅读
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Boolean} is_read 是否阅读
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     */
    public function update($id, UpdateRequest $request)
    {
        $isRead = $request->get('is_read') == 'true' ? 1 : 0;

        $user = \Auth::User();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return $this->response->noContent();
        }

        $notification->is_read = $isRead;

        $notification->save();

        return $this->response->noContent();
    }
    /**
     * @apiGroup Notification
     * @apiDescription  删除一条消息
     *
     * @api {delete} /notifications/{id} 删除一条消息
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     */
    public function destroy($id)
    {
        $user = \Auth::User();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return $this->response->noContent();
        }

        $notification->delete();

        return $this->response->noContent();
    }
    /**
     * @apiGroup Notification
     * @apiDescription  批量删除消息
     *
     * @api {delete} /notifications 批量删除消息
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {array} [ids] 消息ID  如果type不传，ids不能为空；
     * @apiParam {string='all'} [type] 删除方式
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     */
    public function multiDestory(DestoryRequest $request)
    {
        $user = \Auth::User();
        //消息ids
        $notifications = $user->notifications();

        $ids = $request->get('ids');
        $ids = array_filter(array_map('intval', explode(',', $ids)));

        if ($ids) {
            $notifications->whereIn('id', $ids);
        }

        if ($request->get('type') == 'all') {
            $notifications->where('is_read', 1);
        }

        $notifications->delete();

        return $this->response->noContent();
    }
}
