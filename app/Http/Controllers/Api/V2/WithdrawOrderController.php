<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\WithdrawOrder\StoreRequest;
use Illuminate\Http\Request;
use App\Transformers\WithdrawOrderTransformer;
use App\Models\WithdrawOrder;
use App\Models\Transaction;

class WithdrawOrderController extends BaseController
{
    /**
     * @apiGroup  withdraw_orders
     * @apiDescription 创建提现单
     *
     * @api {post} /withdraw/orders 创建提现单
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Number} amount  提现金额
     * @apiParam {String='BANKCARD','PAYPAL','ALIPAY'} type  提现类型
     * @apiParam {String} bank_name  银行
     * @apiParam {String} account_name  申请人
     * @apiParam {String} account_no  帐号
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 created
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::user();

        $existAccountName = $user->account_name;

        $amount = $request->get('amount');
        $accountName = $request->get('account_name');

        if ((float) $amount > $user->amount) {
            return response()->json(['errors' => ['amount' => trans('error.orders.amount')]], 400);
        }

        if ($existAccountName && $accountName != $existAccountName) {
            return response()->json(['errors' => ['account_name' => trans('error.orders.account_name')]], 400);
        }

        \DB::beginTransaction();
        try {
            $order = new WithdrawOrder();

            $type = strtoupper($request->get('type'));
            $order->fill($request->all());
            $order->type = $type;
            $order->user()->associate($user);
            $order->status = 'PENDING_APPROVAL';
            $order->amount = $request->get('amount');

            if ($type == 'BANKCARD') {
                $order->bank_name = $request->get('bank_name');
                $order->account_name = $request->get('account_name');
            }

            $extra = $order->extra;
            $extra['lang'] = \App::getLocale();
            $order->extra = $extra;

            $order->save();

            $amount = $order->getAmount();
            $transaction = new Transaction();
            $transaction->user()->associate($user);
            $transaction->pay_type = 'DEFARA';
            $transaction->amount = (-1) * $amount;
            $transaction->type = 'WITHDRAW';
            $transaction->transactionable()->associate($order);
            $transaction->save();

            $user->amount -= $amount;
            $user->save();

            if (!$existAccountName) {
                $user->account_name = $accountName;
                $user->save();
            }

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollback();

            return $this->response->errorInternal();
        }

        return $this->response->created();
    }

    /**
     * @apiGroup  withdraw_orders
     * @apiDescription 提现单列表
     *
     * @api {get} /withdraw/orders 提现单列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='PENDING_APPROVAL', 'SUCCESSED', 'FAILED', 'CANCEL'} status 按状态搜索,不区分大小写
     * @apiParam {Datetime} start_time 创建时间大于
     * @apiParam {Datetime} end_time 创建时间小于
     * @apiParam {String} keyword 名称或者id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 10,
     *      "user_id": 359,
     *      "amount": "1.00",
     *      "note": "",
     *      "bank_name": "农业银行",
     *      "account_name": "易晓东",
     *      "account_no": "53451235413241",
     *      "receipt_no": "",
     *      "type": "BANKCARD",
     *      "status": "PENDING_APPROVAL",
     *      "extra": null,
     *      "created_at": "2016-04-19 07:53:24",
     *      "deleted_at": null,
     *      "status_label": "审核中"
     *    },
     *    {
     *      "id": 8,
     *      "user_id": 359,
     *      "amount": "1.00",
     *      "note": "",
     *      "bank_name": "农业银行",
     *      "account_name": "易晓东",
     *      "account_no": "53451235413241",
     *      "receipt_no": "",
     *      "type": "BANKCARD",
     *      "status": "PENDING_APPROVAL",
     *      "extra": null,
     *      "created_at": "2016-04-19 07:52:19",
     *      "deleted_at": null,
     *      "status_label": "审核中"
     *    }
     *  ],
     *  "meta": {
     *    "pagination": {
     *      "total": 10,
     *      "count": 2,
     *      "per_page": 2,
     *      "current_page": 1,
     *      "total_pages": 5,
     *      "links": {
     *        "next": "http://xiaodong.com/api/withdraw/orders?page=2"
     *      }
     *    }
     *  }
     *}
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        $status = strtoupper($request->get('status'));
        $allowStatuses = ['PENDING_APPROVAL', 'SUCCESSED', 'FAILED', 'CANCEL'];

        $orders = $user->withdrawOrders();
        // 状态搜索
        if (in_array($status, $allowStatuses)) {
            $orders->where('status', $status);
        }

        // 时间搜索
        if ($startTime = $request->get('start_time')) {
            $orders->where('created_at', '>=', $startTime);
        }
        if ($endTime = $request->get('end_time')) {
            $orders->where('created_at', '<=', $endTime);
        }

        $orders = $orders->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new WithdrawOrderTransformer());
    }
}
