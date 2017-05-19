<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\AppealOrderTransformer;
use App\Http\Requests\Api\AppealOrder\StoreRequest;
use App\Http\Requests\Api\AppealOrder\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\AppealOrder;
use App\Models\AppealOrderIntervene;

class AppealOrderController extends BaseController
{
    /**
     * @apiGroup  appeal_orders
     * @apiDescription 创建申诉单
     *
     * @api {post} /appeal/orders 创建申诉单
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Integer} production_order_id  生产单id
     * @apiParam {String} description 描述
     * @apiParam {Numeric} amount 退款金额
     * @apiParam {Array}  [attachments]  资料
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 created
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::user();

        $productionId = $request->get('production_order_id');

        $productionOrder = $user->productionOrders()->find($productionId);

        if ($productionOrder->status != 'SHIPPED') {
            return $this->response->errorForbidden();
        }

        if ($request->get('amount') > $productionOrder->amount) {
            return response()->json(['errors' => [trans('error.appealOrder.amount')]], 403);
        }

        $order = new AppealOrder();
        $order->fill($request->all());

        $order->appealable()->associate($productionOrder);

        $order->owner_id = $productionOrder->owner_id;
        $order->contact_id = $productionOrder->contact_id;
        $order->name = $productionOrder->owner_order_name;
        $order->orginal_amount = $productionOrder->amount;
        $order->status = 'PENDING_REFUND';

        $order->save();

        $productionOrder->status = 'APPEALED';
        $productionOrder->is_appeal = 1;
        $productionOrder->save();

        //附件处理
        if ($attachments = $request->get('attachments')) {
            $allowed = ['id'];
            array_walk($attachments, function (&$attachments)  use ($allowed) {
                $attachments = array_intersect_key($attachments, array_flip($allowed));
            });
            $order->updateAttachment($attachments, 'detail');
        }

        return $this->response->created();
    }

    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::user();
        $order = $user->appealOrders()->find($id);

        if (!$order) {
            return $this->response->errorForbidden();
        }

        $operate = $request->get('operate');

        $method = camel_case($operate);

        return $this->$method($order);
    }

    /**
     * @apiGroup appeal_orders
     * @apiDescription 申诉单取消
     *
     * @api {put} /appeal/orders/{id}--(cancle) 申诉单取消
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='cancel'} operate 操作为cancel
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function cancel($order)
    {
        if ($order->status != 'PENDING_REFUND') {
            return $this->response->errorForbidden();
        }

        $order->status = 'CANCELED';
        $order->save();

        $productionOrder = $order->appealable;

        $productionOrder->appeal_handled_time = $order->updated_at->diffInSeconds($order->created_at);
        $productionOrder->status = 'SHIPPED';

        $productionOrder->save();

        return $this->response->noContent();
    }

//    /**
//     * @apiGroup appeal_orders
//     * @apiDescription 申诉单退款
//     *
//     * @api {put} /appeal/orders/{id}--(return) 申诉单退款
//     * @apiVersion 0.2.0
//     * @apiPermission jwt
//     * @apiParam {String='return'} operate 操作为return
//     * @apiSuccessExample {json} Success-Response:
//     * HTTP/1.1 204
//     */
//    private function _return($order)
//    {
//        if ($order->status != 'PENDING_REFUND') {
//            return $this->response->errorForbidden();
//        }
//
//        \DB::beginTransaction();
//        try {
//            $order->status = 'FINISHED';
//
//            $order->refund()->save();
//
//            \DB::commit();
//        } catch (\Exception $e) {
//            \DB::rollBack();
//
//            return $this->response->errorInternal();
//        }
//
//        return $this->response->noContent();
//    }
//
//    /**
//     * @apiGroup appeal_orders
//     * @apiDescription 申诉单申请介入
//     *
//     * @api {put} /appeal/orders/{id}--(apply) 申诉单申请介入
//     * @apiVersion 0.2.0
//     * @apiPermission jwt
//     * @apiParam {String='apply'} operate 操作为apply
//     * @apiParam {String} description 描述
//     * @apiParam {Array}  [attachments]  资料
//     * @apiParam {String}  [attachments.type] 类型为：appeal_orders_intervene
//     * @apiParam {String}  [attachments.tag]  标签为：detail
//     * @apiSuccessExample {json} Success-Response:
//     * HTTP/1.1 201 created
//     */
//    private function _apply($order)
//    {
//        if ($order->status != 'PENDING_REFUND') {
//            return $this->response->errorForbidden();
//        }
//
//        $orderIntervene = new AppealOrderIntervene();
//
//        $orderIntervene->fill($request->all());
//        $orderIntervene->appeal_order_id = $order->id;
//        $orderIntervene->status = 'PENDING_DEAL';
//
//        $orderIntervene->save();
//
//        //附件处理
//        if ($attachments = $request->get('attachments')) {
//            $orderIntervene->updateAttachment($attachments, 'detail');
//        }
//
//        $order->status = 'DEFARA_INTERVENE';
//
//        $order->save();
//
//        return $this->response->created();
//    }

    /**
     * @apiGroup  appeal_orders
     * @apiDescription 申诉单列表
     *
     * @api {get} /appeal/orders 申诉单列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='PENDING_REFUND','DEFARA_INTERVENE','CANCELED','FINISHED'} status 按状态搜索,不区分大小写
     * @apiParam {Datetime} [start_time] 创建时间大于
     * @apiParam {Datetime} [end_time] 创建时间小于
     * @apiParam {String} [keyword] 名称或者id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 1,
     *      "owner_id": 359,
     *      "contact_id": 44,
     *      "name": "",
     *      "amount": "10.00",
     *      "description": "dfsadfdasfadsfasdf",
     *      "status": "CANCELED",
     *      "status_label": "已取消",
     *      "created_at": "2016-04-15 06:59:32",
     *      "deleted_at": null,
     *      "contact": {
     *        "data": {
     *          "id": 44,
     *          "type": "MAKER",
     *          "cellphone": "13402847872",
     *          "email": "",
     *          "avatar": "https://defara.imgix.net/userdata/assets/avatars/2015/09/29/7392760ad435ea91af49806736b4df219965b3a6.jpg",
     *          "first_name": "王",
     *          "last_name": "和荣",
     *          "gender": "MALE",
     *          "is_email_verified": 0,
     *          "is_cellphone_verified": 0,
     *          "created_at": "2015-09-01 11:35:22",
     *          "updated_at": "2016-04-12 12:49:17",
     *          "status": "ACTIVE",
     *          "birthday": "2015-09-01 08:00:00",
     *          "is_verify": 1,
     *          "logged_at": null,
     *          "factory_name": "坤翔皮鞋",
     *          "fullname": "王 和荣"
     *        }
     *      }
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
        $user = \Auth::user();
        $orders = $user->appealOrders();
        $status = strtoupper($request->get('status'));
        $allowStatuses = ['PENDING_REFUND', 'DEFARA_INTERVENE', 'CANCELED', 'FINISHED'];

        // 状态搜索
        if (in_array($status, $allowStatuses)) {
            if ($status == 'FINISHED') {
                $orders->where('status', $status)->orWhere('status', 'DEFARA_INTERVENE');
            } else {
                $orders->where('status', $status);
            }
        }

        // 时间搜索
        if ($startTime = $request->get('start_time')) {
            $orders->where('created_at', '>=', $startTime);
        }
        if ($endTime = $request->get('end_time')) {
            $orders->where('created_at', '<=', $endTime);
        }

        //搜索
        if ($keyword = $request->get('keyword')) {
            $orders->where(function ($query) use ($keyword) {
                $query->orwhere('id', $keyword)
                    ->orWhere('name', 'like', '%'.$keyword.'%');
            });
        }

        $orders = $orders->orderBy('updated_at')->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new AppealOrderTransformer($user));
    }

    /**
     * @apiGroup appeal_orders
     * @apiDescription 申诉单详情
     *
     * @api {get} /appeal/orders/{id} 申诉单详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200
     *{
     *  "data": [
     *    {
     *      "id": 1,
     *      "owner_id": 359,
     *      "contact_id": 44,
     *      "name": "",
     *      "amount": "10.00",
     *      "orginal_amount": "0.00",
     *      "description": "dfsadfdasfadsfasdf",
     *      "status": "CANCELED",
     *      "status_label": "已取消",
     *      "created_at": "2016-04-15 06:59:32",
     *      "deleted_at": null,
     *      "contact": {
     *        "data": {
     *          "id": 44,
     *          "type": "MAKER",
     *          "cellphone": "13402847872",
     *          "email": "",
     *          "avatar": "https://defara.imgix.net/userdata/assets/avatars/2015/09/29/7392760ad435ea91af49806736b4df219965b3a6.jpg",
     *          "first_name": "王",
     *          "last_name": "和荣",
     *          "gender": "MALE",
     *          "is_email_verified": 0,
     *          "is_cellphone_verified": 0,
     *          "created_at": "2015-09-01 11:35:22",
     *          "updated_at": "2016-04-12 12:49:17",
     *          "status": "ACTIVE",
     *          "birthday": "2015-09-01 08:00:00",
     *          "is_verify": 1,
     *          "logged_at": null,
     *          "factory_name": "坤翔皮鞋",
     *          "fullname": "王 和荣"
     *        }
     *      }
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
    public function show($id)
    {
        $user = \Auth::user();

        $order = $user->appealOrders()->find($id);

        if (!$order) {
            return $this->response->errorForbidden();
        }

        return $this->response->item($order, new AppealOrderTransformer($user));
    }

    /**
     * @apiGroup appeal_orders
     * @apiDescription 申诉单删除
     *
     * @api {delete} /appeal/orders/{id} 申诉单删除
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    public function delete($id)
    {
        $order = AppealOrder::find($id);
        if ($order->status != 'CANCELED' && $order->status != 'FINISHED') {
            return $this->response->errorForbidden();
        }
        if (!$order) {
            return $this->response->errorNotFound();
        }

        $order->delete();
        return $this->response->noContent();
    }
}
