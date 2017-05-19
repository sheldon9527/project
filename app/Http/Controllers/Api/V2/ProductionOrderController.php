<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\ProductionOrderTransformer;
use App\Http\Requests\Api\ProductionOrder\UpdateRequest;
use Illuminate\Http\Request;

class ProductionOrderController extends BaseController
{
    /**
     * @apiGroup  Production_Order
     * @apiDescription 我的生产订单列表
     *
     * @api {get} /production/orders 我的生产订单列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String=''PENDING_PAY', 'IN_PROGRESS', 'SHIPPED', 'APPEALED', 'EXPIRED', 'FINISHED'} status 按状态搜索,不区分大小写
     * @apiParam {Datetime} start_time 创建时间大于
     * @apiParam {Datetime} end_time 创建时间小于
     * @apiParam {String} keyword 名称或者id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "purchase_order_id": 10,
     *       "owner_id": 362,
     *       "contact_id": 311,
     *       "name": "测试询价",
     *       "cover_picture_url": "",
     *       "style_no": "测试款号",
     *       "category_id": 1,
     *       "price": "100.00",
     *       "duration": 8,
     *       "number": 300,
     *       "track_name": "",
     *       "track_number": "",
     *       "note": "",
     *       "status": "PENDING_PAY",
     *       "created_at": "2016-04-12 11:28:19",
     *       "deleted_at": null,
     *       "contact": {
     *         "data": {
     *           "id": 311,
     *           "type": "MAKER",
     *           "cellphone": "18908185339",
     *           "email": "",
     *           "avatar": "http://local.defara/assets/default/defaultAvatar.jpg",
     *           "first_name": "林",
     *           "last_name": "玉春",
     *           "gender": "MALE",
     *           "is_email_verified": 0,
     *           "is_cellphone_verified": 0,
     *           "created_at": "2016-01-28 04:08:23",
     *           "updated_at": "2016-04-12 04:49:02",
     *           "status": "ACTIVE",
     *           "birthday": "0000-00-00 00:00:00",
     *           "is_verify": 1,
     *           "logged_at": null,
     *           "fullname": "林 玉春"
     *         }
     *       }
     *     },
     *     {
     *       "id": 2,
     *       "purchase_order_id": 11,
     *       "owner_id": 362,
     *       "contact_id": 311,
     *       "name": "测试询价",
     *       "cover_picture_url": "",
     *       "style_no": "测试款号",
     *       "category_id": 1,
     *       "price": "100.00",
     *       "duration": 8,
     *       "number": 300,
     *       "track_name": "foo",
     *       "track_number": "1231239u9283",
     *       "note": "",
     *       "status": "FINISHED",
     *       "created_at": "2016-04-12 11:31:10",
     *       "deleted_at": null,
     *       "contact": {
     *         "data": {
     *           "id": 311,
     *           "type": "MAKER",
     *           "cellphone": "18908185339",
     *           "email": "",
     *           "avatar": "http://local.defara/assets/default/defaultAvatar.jpg",
     *           "first_name": "林",
     *           "last_name": "玉春",
     *           "gender": "MALE",
     *           "is_email_verified": 0,
     *           "is_cellphone_verified": 0,
     *           "created_at": "2016-01-28 04:08:23",
     *           "updated_at": "2016-04-12 04:49:02",
     *           "status": "ACTIVE",
     *           "birthday": "0000-00-00 00:00:00",
     *           "is_verify": 1,
     *           "logged_at": null,
     *           "fullname": "林 玉春"
     *         }
     *       },
     *       "address": {
     *         "data": {
     *           "id": 7,
     *           "contact": "Harsha Mariam Thomas",
     *           "contact_cellphone": "+96893200931",
     *           "contact_email": null,
     *           "country_region_id": 2598,
     *           "province_region_id": 2606,
     *           "city_region_id": 0,
     *           "address": "3rd Floor, Flat No.7, Al Haq Building, Liya M",
     *           "postcode": "211",
     *           "note": "3rd Floor, Flat No.7, Al Haq Building, Liya Medical Complex"
     *         }
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "pagination": {
     *       "total": 2,
     *       "count": 2,
     *       "per_page": 20,
     *       "current_page": 1,
     *       "total_pages": 1,
     *       "links": []
     *     }
     *   }
     * }
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        $orders = $user->productionOrders();
        $status = strtoupper($request->get('status'));
        $allowStatuses = ['PENDING_PAY', 'IN_PROGRESS', 'SHIPPED', 'APPEALED', 'EXPIRED', 'FINISHED', 'CANCELED'];

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

        //搜索
        if ($keyword = $request->get('keyword')) {
            $orders->where(function ($query) use ($keyword) {
                $query->orwhere('id', $keyword)
                    ->orwhere('id', (int) substr($keyword, 2))
                    ->orWhere('owner_order_name', 'like', '%'.$keyword.'%')
                    ->orWhere('contact_order_name', 'like', '%'.$keyword.'%');
            });
        }

        $orders = $orders->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new ProductionOrderTransformer($user));
    }

    /**
     * @apiGroup  Production_Order
     * @apiDescription 我的生产订单详情
     *
     * @api {get} /production/orders/{id} 我的生产订单详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string='contract', 'comments','invoice','packOrder'} [include]  包含的信息
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 ok
     * {
     *   "data": {
     *     "id": 2,
     *     "purchase_order_id": 11,
     *     "owner_id": 362,
     *     "contact_id": 311,
     *     "name": "测试询价",
     *     "cover_picture_url": "",
     *     "style_no": "测试款号",
     *     "category_id": 1,
     *     "price": "100.00",
     *     "duration": 8,
     *     "number": 300,
     *     "track_name": "foo",
     *     "track_number": "1231239u9283",
     *     "note": "",
     *     "status": "FINISHED",
     *     "created_at": "2016-04-12 11:31:10",
     *     "deleted_at": null,
     *     "contact": {
     *       "data": {
     *         "id": 311,
     *         "type": "MAKER",
     *         "cellphone": "18908185339",
     *         "email": "",
     *         "avatar": "http://local.defara/assets/default/defaultAvatar.jpg",
     *         "first_name": "林",
     *         "last_name": "玉春",
     *         "gender": "MALE",
     *         "is_email_verified": 0,
     *         "is_cellphone_verified": 0,
     *         "created_at": "2016-01-28 04:08:23",
     *         "updated_at": "2016-04-12 04:49:02",
     *         "status": "ACTIVE",
     *         "birthday": "0000-00-00 00:00:00",
     *         "is_verify": 1,
     *         "logged_at": null,
     *         "fullname": "林 玉春"
     *       }
     *     },
     *   }
     * }
     */
    public function show($id)
    {
        $user = \Auth::user();

        $order = $user->productionOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($order, new ProductionOrderTransformer($user));
    }

    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::user();
        $order = $user->productionOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        $operate = $request->get('operate');

        $method = camel_case($operate);

        return $this->$method($order, $request);
    }

        /**
         * @apiGroup Production_Order
         * @apiDescription 生产订单取消
         *
         * @api {put} /production/orders/{id}/update(cancel) 生产订单取消
         * @apiVersion 0.2.0
         * @apiPermission jwt
         * @apiParam {String='cancel'} operate 操作为 cancel
         * @apiSuccessExample {json} Success-Response:
         * HTTP/1.1 204
         */
        private function cancel($order, $request)
        {
            if (!$order->checkContact(\Auth::user()) || $order->status != 'PENDING_PAY') {
                return $this->response->errorForbidden();
            }
            $order->status = 'CANCELED';
            $order->save();

            return $this->response->noContent();
        }

    /**
     * @apiGroup Production_Order
     * @apiDescription 生产订单发货
     *
     * @api {put} /production/orders/{id}/update(ship) 生产订单发货
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='ship'} operate 操作为ship
     * @apiParam {String} track_name 物流公司
     * @apiParam {String} track_number 物流单号
     * @apiParam {String} invoice 发票attachment tag=invoice
     * @apiParam {String} pack_order 装箱单attachment tag=pack_order
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function ship($order, $request)
    {
        if (!$order->checkContact(\Auth::user()) || $order->status != 'IN_PROGRESS') {
            return $this->response->errorForbidden();
        }

        $order->fill($request->input());
        $order->status = 'SHIPPED';
        // 发票
        if ($invoice = $request->get('invoice')) {
            $order->updateAttachment($invoice, 'invoice');
        }
        // 装箱单
        if ($packOrder = $request->get('pack_order')) {
            $order->updateAttachment($packOrder, 'pack_order');
        }

        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup Production_Order
     * @apiDescription 生产订单收货
     *
     * @api {put} /production/orders/{id}/update(accept) 生产订单收货
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='accept'} operate 操作为 accept
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function accept($order, $request)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'SHIPPED') {
            return $this->response->errorForbidden();
        }

        // 完成，付款给制造商
        \DB::beginTransaction();
        try {
            $order->status = 'FINISHED';
            $order->settleUp()->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }
}
