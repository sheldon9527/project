<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Transformers\OrderMessageTransformer;

class OrderMessageController extends BaseController
{
    /**
     * @apiGroup Messages
     * @apiDescription  订单消息列表
     *
     * @api {get} /order/messages 订单消息列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 18,
     *      "user_id": 386,
     *      "order_type": "SERVICE",
     *      "order_status": "SUBMITTED",
     *      "created_at": "2016-05-26 07:59:36",
     *      "updated_at": "2016-05-26 07:59:36",
     *      "type": "service_order",
     *      "type_id": 30,
     *      "order_type_label": "Service Order",
     *      "order_message_label": "You have a design order waiting for checking tech pack. "
     *    }
     *  ],
     *  "meta": {
     *    "pagination": {
     *      "total": 1,
     *      "count": 1,
     *      "per_page": 20,
     *      "current_page": 1,
     *      "total_pages": 1,
     *      "links": []
     *    }
     *  }
     *}
     */
    public function index(Request $request)
    {
        $user = \Auth::User();

        $messages = $user->orderMessages();

        $messages = $messages->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response()->paginator($messages, new OrderMessageTransformer());
    }
    /**
     * @apiGroup Messages
     * @apiDescription  订单消息阅读
     *
     * @api {delete} /order/messages/{id} 订单消息阅读
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     */
    public function destroy($id)
    {
        $user = \Auth::User();

        $message = $user->orderMessages()->find($id);

        if (!$message) {
            return $this->response->errorNotFound();
        }

        $message->delete();

        return $this->response->noContent();
    }
}
