<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\ServiceOrder\StoreRequest;
use App\Http\Requests\Api\ServiceOrder\UpdateRequest;
use Illuminate\Http\Request;
use App\Transformers\ServiceOrderTransformer;
use App\Models\Service;
use App\Models\Contract;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;

class ServiceOrderController extends BaseController
{
    /**
     * @apiGroup service_orders
     * @apiDescription 创建委托设计订单
     *
     * @api {post} /service/orders 创建委托设计订单
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='spring_summer','fall_winter'} service_type  委托设计订单作品所属季节
     * @apiParam {Integer} service_id  某个服务id
     * @apiParam {String} [description]  委托设计订单描述
     * @apiParam {Array} [attachments]  委托设计订单提供图片 tag =detail
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 created
     */
    public function store(StoreRequest $request)
    {
        $service = Service::find($request->get('service_id'));

        if (!$service) {
            return $this->response->errorNotFound();
        }

        $user = \Auth::user();
        $order = new ServiceOrder();
        $order->owner_id = $user->id;
        $order->contact_id = $service->user_id;
        $order->service_id = $service->id;
        $order->amount = $service->price;
        $order->description = $request->get('description');
        $order->service_type = strtoupper($request->get('service_type'));
        $order->status = 'PENDING_PAY';

        // 合同必须是同类型的
        $contract = Contract::where('type', 'service')->orderBy('version', 'desc')->first();
        if ($contract) {
            $order->contract()->associate($contract);
        }

        $order->save();

        //附件处理
        if ($attachments = $request->get('attachments')) {
            $order->updateAttachment($attachments, 'detail');
        }

        $orderItem = new ServiceOrderItem();

        $orderItem->user_id = $user->id;
        $orderItem->service_id = $service->id;
        $orderItem->service_order_id = $order->id;
        $orderItem->category_id = $service->category_id;
        $orderItem->custom_category = $service->custom_category ?: '';
        $orderItem->cover_picture_url = $service->cover_picture_url;
        $orderItem->name = $service->name;
        $orderItem->en_name = $service->en_name;
        $orderItem->description = $service->description;
        $orderItem->en_description = $service->en_description;
        $orderItem->price = $service->price;

        $orderItem->save();

        return $this->response->item($order, new ServiceOrderTransformer($user));
    }

    /**
     * @apiGroup service_orders
     * @apiDescription 委托设计订单更新
     *
     * @api {put} /service/orders/{id} 委托设计订单更新
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='cancel','draft_submit','confirm','submit','deliver','accept'} operate  委托设计订单操作
     * @apiParam {String} [contact_note]  设计师提交草稿时的备注
     * @apiParam {Array} [attachments]  委托设计订单提供图片 tag =first 初稿, final 终稿
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::user();

        $order = $user->serviceOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        $operate = $request->get('operate');

        $method = '_'.camel_case($operate);

        return $this->$method($order);
    }

    /**
     * @apiGroup service_orders
     * @apiDescription 取消服务订单
     *
     * @api {put} /service/orders/{id}--(cancle) 取消服务订单
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='cancel'} operate 操作为cancel
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function _cancel($order)
    {
        if ($order->checkContact(\Auth::user())) {
            return $this->_contactCancel($order);
        } else {
            return $this->_ownerCancel($order);
        }
    }

    private function _contactCancel($order)
    {
        if ($order->status != 'PAID') {
            return $this->response->errorForbidden();
        }

        \DB::beginTransaction();
        try {
            $order->status = 'CONTACT_CANCELED';

            $order->refund()->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }

    //制造商/小B 取消
    private function _ownerCancel($order)
    {
        if ($order->status != 'DRAFT_SUBMITTED') {
            return $this->response->errorForbidden();
        }

        \DB::beginTransaction();
        try {
            $order->status = 'OWNER_CANCELED';
            $order->refund()->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }

    /**
     * @apiGroup service_orders
     * @apiDescription 设计师接受
     *
     * @api {put} /service/orders/{id}--(accept) 设计师接受
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function _accept($order)
    {
        if (!$order->checkContact(\Auth::user()) || $order->status != 'PAID') {
            return $this->response->errorForbidden();
        }

        $order->status = 'ACCEPTED';

        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup service_orders
     * @apiDescription 提交草稿
     *
     * @api {put} /service/orders/{id}--(draft_submit) 提交草稿
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='draft_submit'} operate  委托设计订单操作
     * @apiParam {String} [draft_note]  设计师提交草稿时的备注
     * @apiParam {Array} [attachments]  委托设计订单提供图片 tag =first 初稿
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function _draftSubmit($order)
    {
        if (!$order->checkContact(\Auth::user()) || $order->status != 'ACCEPTED') {
            return $this->response->errorForbidden();
        }

        $extra = $order->extra;
        $extra['draft_note'] = (string) $request->get('draft_note') ?: null;

        $order->status = 'DRAFT_SUBMITTED';
        $order->extra = $extra;

        //附件处理
        if ($attachments = $request->get('attachments')) {
            $order->updateAttachment($attachments, 'first');
        }

        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup service_orders
     * @apiDescription 联系人确认
     *
     * @api {put} /service/orders/{id}--(confirm) 联系人确认
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function _confirm($order)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'DRAFT_SUBMITTED') {
            return $this->response->errorForbidden();
        }

        $order->status = 'CONFIRMED';

        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup service_orders
     * @apiDescription 提交终稿
     *
     * @api {put} /service/orders/{id}--(submit) 提交终稿
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='submit'} operate  委托设计订单操作
     * @apiParam {String} [last_note]  设计师提交草稿时的备注
     * @apiParam {Array} [attachments]  委托设计订单提供图片 tag =final 终稿
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function _submit($order)
    {
        if (!$order->checkContact(\Auth::user()) || $order->status != 'CONFIRMED') {
            return $this->response->errorForbidden();
        }

        $extra = $order->extra;
        $extra['last_note'] = (string) $request->get('last_note') ?: null;

        $order->status = 'SUBMITTED';
        $order->extra = $extra;

        $order->save();

        //附件处理
        if ($attachments = $request->get('attachments')) {
            $order->updateAttachment($attachments, 'final');
        }

        return $this->response->noContent();
    }

    /**
     * @apiGroup service_orders
     * @apiDescription 联系人收货
     *
     * @api {put} /service/orders/{id}--(deliver) 联系人收货
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function _deliver($order)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'SUBMITTED') {
            return $this->response->errorForbidden();
        }

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

    /**
     * @apiGroup service_orders
     * @apiDescription 委托服务订单列表
     *
     * @api {get} /service/orders 委托服务订单列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} [include]  可引入的关系
     * @apiParam {String} [include.item]  设计师创建的服务
     * @apiParam {String} [include.attachments] 委托服务的订单附件
     * @apiParam {string} [per_page]  可选分页
     * @apiParam {string} [start_time]  开始时间
     * @apiParam {string} [end_time]  结束时间
     * @apiParam {string} [keyword]  搜索字段
     * @apiParam {string='PENDING_PAY','PAID','CANCELED','DRAFT_SUBMITTED','CONFIRMED','SUBMITTED','FINISHED','ACCEPTED'} [status]  订单状态
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 1,
     *      "owner_id": 185,
     *      "contact_id": 9,
     *      "service_id": 31,
     *      "amount": 88,
     *      "service_type": "SPRING_SUMMER",
     *      "description": "fdggfsdgsdfgsdfg",
     *      "status": "PENDING_PAY",
     *      "extra": null,
     *      "created_at": "2015-09-01 08:00:00",
     *      "deleted_at": null,
     *      "item": {
     *        "data": {
     *          "service_id": 31,
     *          "service_order_id": 1,
     *          "category_id": 3,
     *          "custom_category": "55",
     *          "cover_picture_url": "https://defara.imgix.net/userdata/assets/avatars/2015/09/29/856e256ef49da2bf4d99ca0d757f5eb2de950a96.jpg",
     *          "name": "sdfdafasd",
     *          "description": "sdfasdfasdfasdfasfa",
     *          "price": "88.00",
     *          "category": {
     *            "data": {
     *              "id": 3,
     *              "parent_id": 1,
     *              "name": "Coats & Jackets"
     *            }
     *          }
     *        }
     *      },
     *      "owner": {
     *        "data": {
     *          "id": 185,
     *          "type": "MAKER",
     *          "cellphone": null,
     *          "email": "michelle@beachsociety.co.uk",
     *          "avatar": "http://foobar.com",
     *          "first_name": null,
     *          "last_name": null,
     *          "gender": null,
     *          "is_email_verified": 1,
     *          "is_cellphone_verified": 0,
     *          "created_at": "2015-11-04 02:36:25",
     *          "updated_at": "2016-04-01 10:34:22",
     *          "amount": "0.00",
     *          "status": "INACTIVE",
     *          "birthday": null,
     *          "privilege_amount": 0,
     *          "is_verify": 1,
     *          "logged_at": null,
     *          "fullname": null
     *        }
     *      },
     *      "contact": {
     *        "data": {
     *          "id": 9,
     *          "type": "DESIGNER",
     *          "cellphone": "18482332878",
     *          "email": null,
     *          "avatar": "https://defara.imgix.net/userdata/assets/avatars/2015/09/02/453cc263185aedf07e330758b209da8885cc1e3b.jpg",
     *          "first_name": "",
     *          "last_name": "",
     *          "gender": "SECRET",
     *          "is_email_verified": 0,
     *          "is_cellphone_verified": 1,
     *          "created_at": "2015-08-03 10:05:56",
     *          "updated_at": "2016-04-05 05:24:59",
     *          "amount": "0.00",
     *          "status": "INACTIVE",
     *          "birthday": "0000-00-00 00:00:00",
     *          "privilege_amount": 0,
     *          "is_verify": 1,
     *          "logged_at": "2016-04-05 05:24:59",
     *          "fullname": null
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
        $orders = $user->serviceOrders();

        //开始时间
        if ($startTime = $request->get('start_time')) {
            $orders->where('service_orders.created_at', '>=', $startTime);
        }

        //结束时间
        if ($endTime = $request->get('end_time')) {
            $orders->where('service_orders.created_at', '<=', $endTime);
        }

        //搜索
        if ($keyword = $request->get('keyword')) {
            $orders->leftJoin('service_order_items', 'service_orders.id', '=', 'service_order_items.service_order_id');
            $orders->where(function ($query) use ($keyword) {
                $query->orwhere('service_orders.id', $keyword)
                    ->orwhere('service_orders.id', (int) substr($keyword, 2))
                    ->orWhere('service_order_items.name', 'like', '%'.$keyword.'%')
                    ->orWhere('service_order_items.en_name', 'like', '%'.$keyword.'%');
            });
        }

        //状态
        $status = strtoupper($request->get('status'));
        $allowStatuses = ['PENDING_PAY', 'PAID', 'CANCELED', 'DRAFT_SUBMITTED', 'CONFIRMED', 'SUBMITTED', 'FINISHED', 'ACCEPTED'];
        if (in_array($status, $allowStatuses)) {
            if ($status == 'CANCELED') {
                $orders->where(function ($query) use ($status) {
                    $query->orwhere('service_orders.status', 'OWNER_CANCELED')
                        ->orWhere('service_orders.status', 'CONTACT_CANCELED');
                });
            } else {
                $orders->where('service_orders.status', $status);
            }
        }

        $orders = $orders->orderBy('service_orders.updated_at', 'desc')
            ->select('service_orders.*')
            ->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new ServiceOrderTransformer($user));
    }

    /**
     * @apiGroup service_orders
     * @apiDescription 委托服务订单详情
     *
     * @api {get} /service/orders/{id} 委托服务订单详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} [include]  可引入的关系
     * @apiParam {String} [include.item]  服务
     * @apiParam {String} [include.details]  服务附件
     * @apiParam {String} [include.fristDrafts]  初稿
     * @apiParam {String} [include.finalDrafts]  终稿
     * @apiParam {String} [include.contract]  合同
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": {
     *    "id": 1,
     *    "owner_id": 185,
     *    "contact_id": 9,
     *    "service_id": 31,
     *    "amount": 88,
     *    "service_type": "SPRING_SUMMER",
     *    "description": "fdggfsdgsdfgsdfg",
     *    "status": "PENDING_PAY",
     *    "extra": null,
     *    "created_at": "2015-09-01 08:00:00",
     *    "deleted_at": null,
     *    "details": {
     *      "data": [
     *        {
     *          "id": 15,
     *          "user_id": 49,
     *          "relative_path": "assets/consulting/2015/09/01/cd63f03ce54c32fd6e1953fad0b0cb046a770019.jpg",
     *          "filename": "999999.jpg",
     *          "description": "",
     *          "tag": "detail",
     *          "mime_types": "image/jpeg",
     *          "created_at": "2015-09-01 16:36:00",
     *          "updated_at": "2016-04-01 10:34:49",
     *          "url": "https://defara.imgix.net/userdata/assets/consulting/2015/09/01/cd63f03ce54c32fd6e1953fad0b0cb046a770019.jpg"
     *        },
     *        {
     *          "id": 16,
     *          "user_id": 49,
     *          "relative_path": "assets/consulting/2015/09/01/7ec79edd45fdd6cc1a28e552be113ae726a03189.jpg",
     *          "filename": "sz.jpg",
     *          "description": "",
     *          "tag": "detail",
     *          "mime_types": "image/jpeg",
     *          "created_at": "2015-09-01 16:34:57",
     *          "updated_at": "2016-04-01 10:34:49",
     *          "url": "https://defara.imgix.net/userdata/assets/consulting/2015/09/01/7ec79edd45fdd6cc1a28e552be113ae726a03189.jpg"
     *        }
     *      ]
     *    },
     *    "fristDrafts": {
     *      "data": [
     *        {
     *          "id": 10,
     *          "user_id": 49,
     *          "relative_path": "assets/consulting/2015/09/01/7ec79edd45fdd6cc1a28e552be113ae726a03189.jpg",
     *          "filename": "sz.jpg",
     *          "description": "",
     *          "tag": "first",
     *          "mime_types": "image/jpeg",
     *          "created_at": "2015-09-01 16:34:57",
     *          "updated_at": "2016-04-01 10:34:49",
     *          "url": "https://defara.imgix.net/userdata/assets/consulting/2015/09/01/7ec79edd45fdd6cc1a28e552be113ae726a03189.jpg"
     *        }
     *      ]
     *    },
     *    "finalDrafts": {
     *      "data": [
     *        {
     *          "id": 13,
     *          "user_id": 49,
     *          "relative_path": "assets/consulting/2015/09/01/6446ddf9082f311cbabe61911651f189f45841ee.jpg",
     *          "filename": "44444.jpg",
     *          "description": "",
     *          "tag": "final",
     *          "mime_types": "image/jpeg",
     *          "created_at": "2015-09-01 16:35:36",
     *          "updated_at": "2016-04-01 10:34:49",
     *          "url": "https://defara.imgix.net/userdata/assets/consulting/2015/09/01/6446ddf9082f311cbabe61911651f189f45841ee.jpg"
     *        }
     *      ]
     *    }
     *  }
     *}
     */
    public function show($id, Request $request)
    {
        $user = \Auth::user();

        $order = ServiceOrder::find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        if ($user->id != $order->owner_id && $user->id != $order->contact_id) {
            return $this->response->errorForbidden();
        }

        return $this->response->item($order, new ServiceOrderTransformer($user));
    }
}
