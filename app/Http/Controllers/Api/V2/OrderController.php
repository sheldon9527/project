<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Transformers\BaseTransformer;

class OrderController extends BaseController
{
    /**
     * @apiGroup Payment
     * @apiDescription 查看订单详情
     *
     * @api {get} /order/{orderNo} 查看订单详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} order_no 订单编号
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": {
     *    "id": 1,
     *    "owner_id": 378,
     *    "contact_id": 311,
     *    "contract_id": 0,
     *    "inquiry_order_id": 1,
     *    "name": "",
     *    "amount": "1.00",
     *    "duration": 1,
     *    "cover_picture_url": "",
     *    "style_no": "",
     *    "category_id": 0,
     *    "product_weight": 0,
     *    "production_no": 0,
     *    "production_duration": 0,
     *    "production_price": "0.00",
     *    "submit_count": 0,
     *    "note": null,
     *    "status": "PENDING_PAY",
     *    "extra": null,
     *    "created_at": "2015-01-01 00:00:01",
     *    "updated_at": "2015-01-01 00:00:01",
     *    "status_label": "待付款",
     *    "expired_at": "2015-01-02 00:00:01"
     *  }
     * }
     */
    public function show($orderNo, Request $request)
    {
        $user = \Auth::user();
        $order = Order::findByNo($orderNo);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        // 用于付款的订单详情
        if ($order->owner_id != $user->id) {
            return $this->response->errorForbidden();
        }

        $order->order_type = Order::getOrderType($orderNo);
        $orderType = substr($orderNo, 0, 2);

        if ($orderType == 'IS') {
            $order->cover_picture_url = $order->getCloudUrl($order->category ? $order->category->icon_url : '');
        }

        if ($orderType == 'SP') {
            $order->title = $order->owner_order_name;
        }

        if ($orderType == 'PD') {
            $order->title = $order->owner_order_name;
            $order->amount = $order->getAmount();
        }

        $response = $this->response->item($order, new BaseTransformer());

        $response->setMeta(['amount' => $user->amount]);

        return $response;
    }
}
