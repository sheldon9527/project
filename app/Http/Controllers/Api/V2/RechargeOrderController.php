<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\RechargeOrderTransformer;
use App\Http\Requests\Api\RechargeOrder\StoreRequest;
use App\Models\RechargeOrder;
use PayPal;
use Illuminate\Http\Request;
use App\Traits\OrderPay;

class RechargeOrderController extends BaseController
{
    use OrderPay;
    /**
     * @apiGroup  recharge_orders
     * @apiDescription 创建充值单
     *
     * @api {post} /recharge/orders 创建充值单
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Number} amount  充值金额
     * @apiParam {String='PAYPAL','ALIPAY'} type  充值类型
     * @apiParam {String} success_return_url 支付成功后跳转的地址
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200
     *{
     *  "data": {
     *    "user_id": 359,
     *    "amount": "100",
     *    "type": "ALIPAY",
     *    "status": "PENDING_PAY",
     *    "updated_at": "2016-04-20 07:21:41",
     *    "created_at": "2016-04-20 07:21:41",
     *    "id": 3
     *  }
     *}
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::user();

        $order = new RechargeOrder();
        $type = $request->get('type');

        $order->user_id = $user->id;
        $order->amount = $request->get('amount');
        $order->type = strtoupper($type);
        $order->status = 'PENDING_PAY';

        $order->save();

        // 成功后的跳转地址，ios怎么用还得仔细考虑

        $successReturnUrl = $request->get('success_return_url');
        \Cache::store('database')->put('payment-success-'.$order->order_no, $successReturnUrl, 30);

        // 创建成功充值订单后，直接跳转，以后得考虑ios相关的东西，这样做不好
        $type = strtolower($type).'Pay';

        return $this->$type($order, $request);
    }

    /**
     * @apiGroup  recharge_orders
     * @apiDescription 充值单列表
     *
     * @api {get} /recharge/orders 充值单列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Datetime} start_time 创建时间大于
     * @apiParam {Datetime} end_time 创建时间小于
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": [
     *    {
     *      "id": 3,
     *      "user_id": 359,
     *      "amount": "100.00",
     *      "type": "ALIPAY",
     *      "status": "PENDING_PAY",
     *      "created_at": "2016-04-20 07:21:41",
     *      "updated_at": "2016-04-20 07:21:41",
     *      "status_label": "待充值"
     *    },
     *    {
     *      "id": 2,
     *      "user_id": 359,
     *      "amount": "100.00",
     *      "type": "ALIPAY",
     *      "status": "PENDING_PAY",
     *      "created_at": "2016-04-20 07:21:22",
     *      "updated_at": "2016-04-20 07:21:22",
     *      "status_label": "待充值"
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
    public function index(Request $request)
    {
        $user = \Auth::user();

        $orders = $user->rechargeOrders();

        // 时间搜索
        if ($startTime = $request->get('start_time')) {
            $orders->where('created_at', '>=', $startTime);
        }
        if ($endTime = $request->get('end_time')) {
            $orders->where('created_at', '<=', $endTime);
        }

        $orders = $orders->where('status', 'SUCCESSED')->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new RechargeOrderTransformer());
    }
}
