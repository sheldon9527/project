<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\PurchaseOrderTransformer;
use App\Http\Requests\Api\PurchaseOrder\StoreRequest;
use App\Http\Requests\Api\PurchaseOrder\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class PurchaseOrderController extends BaseController
{
    /**
     * @apiGroup  Purchase_Order
     * @apiDescription 创建po单
     *
     * @api {post} /purchase/orders 创建po单
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Integer} sample_order_id  打样单id
     * @apiParam {Integer} [address_id]  收货地址，如果不传则是打样单的地址
     * @apiParam {Integer} production_duration  生产时间
     * @apiParam {Number} production_price  生产价格
     * @apiParam {Number} transport_method  如果不是普通快递
     * @apiParam {Number} is_normal_transport  是否是普通快递
     * @apiParam {array} size_table  attachment array 尺码表 tag=size_table
     * @apiParam {array} auxiliary_datas  attachment array 辅助资料 tag=auxiliary_datas
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 created
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::user();

        $sampleId = $request->get('sample_order_id');

        $sampleOrder = $user->sampleOrders()->find($sampleId);

        $allowStatuses = ['SUBMITTED', 'FINISHED'];

        if (!in_array($sampleOrder->status, $allowStatuses)) {
            return $this->response->errorForbidden();
        }

        $standards = (array) $request->get('production_standards');

        $allowed = ['size', 'color', 'number'];
        array_walk($standards, function (&$standard) use ($allowed) {
            $standard = array_intersect_key($standard, array_flip($allowed));
        });

        $number = array_sum(array_column($standards, 'number'));

        $purchaseOrder = new PurchaseOrder();

        $extra['standards'] = $standards;
        $extra['is_normal_transport'] = $request->get('is_normal_transport') ?: false;

        if (!$extra['is_normal_transport']) {
            $extra['transport_method'] = $request->get('transport_method');
        }

        $requestData = array_merge($sampleOrder->toArray(), $request->input());
        $purchaseOrder->fill($requestData);
        $purchaseOrder->production_duration = $request->get('production_duration');
        $purchaseOrder->extra = $extra;
        $purchaseOrder->production_no = $number;
        $purchaseOrder->sample_order_id = $sampleOrder->id;
        $purchaseOrder->owner_id = $sampleOrder->owner_id;
        $purchaseOrder->contact_id = $sampleOrder->contact_id;
        $purchaseOrder->status = 'IN_PROGRESS';

        $purchaseOrder->save();

        //创建订单与地址
        // 如果传了address_id 说明是重新选地址，不然就是打样单的地址
        if ($addressId = $request->get('address_id')) {
            $userAddress = $user->addresses()->find($addressId);

            //创建订单地址
            $purchaseOrder->updateAddress($userAddress->toArray());
        } else {
            //创建订单与地址
            $sampleAddress = $sampleOrder->address;
            $purchaseOrder->updateAddress($sampleAddress->toArray());
        }

        //更新尺码表
        if ($sizeTable = $request->get('size_table')) {
            $purchaseOrder->updateAttachment([$sizeTable], 'size_table');
        }

        //更新辅助资料
        if ($auxiliaryDatas = $request->get('auxiliary_datas')) {
            $purchaseOrder->updateAttachment($auxiliaryDatas, 'auxiliary_datas');
        }

        //打样单处理
        if ($sampleOrder->status == 'SUBMITTED') {
            \DB::beginTransaction();
            try {
                $record = $sampleOrder->records()
                    ->where('status', 'PENDING_CONFIRMED')
                    ->update(['status' => 'SATISFIED', 'confirmed_at' => Carbon::now()]);

                $sampleOrder->status = 'FINISHED';
                $sampleOrder->settleUp()->save();

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::info($e->getMessage());

                return $this->response->errorInternal();
            }
        }

        return $this->response->created();
    }

    /**
     * @apiGroup  Purchase_Order
     * @apiDescription PO单列表
     *
     * @api {get} /purchase/orders PO单列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='IN_PROGRESS', 'CONFIRMED', 'CANCELED', 'EXPIRED', 'FINISHED'}} status 按状态搜索,不区分大小写
     * @apiParam {Datetime} start_time 创建时间大于
     * @apiParam {Datetime} end_time 创建时间小于
     * @apiParam {String} keyword 名称或者id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": [
     *    {
     *      "id": 1,
     *      "owner_id": 359,
     *      "contact_id": 346,
     *      "sample_order_id": 1,
     *      "name": "po",
     *      "cover_picture_url": "",
     *      "style_no": "fadfasdfa",
     *      "category_id": 2,
     *      "product_weight": 4,
     *      "production_no": 123424,
     *      "production_duration": 22,
     *      "production_price": "99.00",
     *      "note": "rgvsfdvbfgdfadsfasdfasdfasdfasdf",
     *      "status": "IN_PROGRESS",
     *      "extra": null,
     *      "created_at": "2016-04-13 14:22:30",
     *      "deleted_at": null,
     *      "contact": {
     *        "data": {
     *          "id": 346,
     *          "type": "MAKER",
     *          "cellphone": "13958144681",
     *          "email": "",
     *          "avatar": "http://xiaodong.com/assets/default/defaultAvatar.jpg",
     *          "first_name": "张",
     *          "last_name": "宁",
     *          "gender": "MALE",
     *          "is_email_verified": 0,
     *          "is_cellphone_verified": 0,
     *          "created_at": "2016-03-02 17:06:01",
     *          "updated_at": "2016-04-12 12:49:02",
     *          "status": "INACTIVE",
     *          "birthday": "0000-00-00 00:00:00",
     *          "is_verify": 1,
     *          "logged_at": null,
     *          "fullname": "张 宁"
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

        $orders = $user->purchaseOrders();
        $status = strtoupper($request->get('status'));
        $allowStatuses = ['IN_PROGRESS', 'CONFIRMED', 'CANCELED', 'EXPIRED', 'FINISHED'];

        // 状态搜索
        if (in_array($status, $allowStatuses)) {
            if ($status == 'CANCELED') {
                $orders->where(function ($query) use ($status) {
                    $query->orwhere('status', 'OWNER_CANCELED')
                        ->orWhere('status', 'CONTACT_CANCELED');
                });
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
                    ->orwhere('id', (int) substr($keyword, 2))
                    ->orWhere('name', 'like', '%'.$keyword.'%');
            });
        }

        $orders = $orders->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new PurchaseOrderTransformer($user));
    }

    /**
     * @apiGroup  Purchase_Order
     * @apiDescription PO单详情
     *
     * @api {get} /purchase/orders/{id} PO单详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string='address', 'size_table', 'auxiliary_datas', 'category'} [include]  包含的信息
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 ok
     *{
     *  "data": {
     *    "id": 1,
     *    "owner_id": 359,
     *    "contact_id": 346,
     *    "sample_order_id": 1,
     *    "name": "po",
     *    "cover_picture_url": "",
     *    "style_no": "fadfasdfa",
     *    "category_id": 2,
     *    "product_weight": 4,
     *    "production_no": 123424,
     *    "production_duration": 22,
     *    "production_price": "99.00",
     *    "note": "rgvsfdvbfgdfadsfasdfasdfasdfasdf",
     *    "status": "IN_PROGRESS",
     *    "extra": null,
     *    "created_at": "2016-04-13 14:22:30",
     *    "deleted_at": null,
     *    "contact": {
     *      "data": {
     *        "id": 346,
     *        "type": "MAKER",
     *        "cellphone": "13958144681",
     *        "email": "",
     *        "avatar": "http://xiaodong.com/assets/default/defaultAvatar.jpg",
     *        "first_name": "张",
     *        "last_name": "宁",
     *        "gender": "MALE",
     *        "is_email_verified": 0,
     *        "is_cellphone_verified": 0,
     *        "created_at": "2016-03-02 17:06:01",
     *        "updated_at": "2016-04-12 12:49:02",
     *        "status": "INACTIVE",
     *        "birthday": "0000-00-00 00:00:00",
     *        "is_verify": 1,
     *        "logged_at": null,
     *        "fullname": "张 宁"
     *      }
     *    },
     *    "address": {
     *      "data": {
     *        "id": 1,
     *        "contact": "的方法",
     *        "contact_cellphone": "12324324",
     *        "contact_email": "dadad@defafa",
     *        "country_region_id": 3,
     *        "province_region_id": 4,
     *        "city_region_id": 2,
     *        "address": "erqwerqwerqwer",
     *        "postcode": "rqwerqwer",
     *        "note": null
     *      }
     *    },
     *    "size_table": {
     *      "data": [
     *        {
     *          "id": 1151,
     *          "user_id": 359,
     *          "relative_path": "https://defara.imgix.net/userdata/assets/profile/2016/01/05/3b7e86cdaa87e7624c5b2ddc9fde6b302bdb97a1.jpg",
     *          "filename": "巧奇_副本.jpg",
     *          "description": null,
     *          "tag": "size_table",
     *          "mime_types": "image/jpeg",
     *          "created_at": "2016-01-05 15:33:11",
     *          "updated_at": "2016-04-12 12:49:27"
     *        }
     *      ]
     *    },
     *    "auxiliary_datas": {
     *      "data": [
     *        {
     *          "id": 1152,
     *          "user_id": 359,
     *          "relative_path": "https://defara.imgix.net/userdata/assets/profile/2016/01/05/3b3d206aea34cb09a3485ddf00c4228cbde3f024.jpg",
     *          "filename": "尚娜_副本.jpg",
     *          "description": null,
     *          "tag": "auxiliary_datas",
     *          "mime_types": "image/jpeg",
     *          "created_at": "2016-01-05 15:33:44",
     *          "updated_at": "2016-04-12 12:49:27"
     *        }
     *      ]
     *    }
     *  }
     *}
     */
    public function show($id)
    {
        $user = \Auth::user();

        $order = $user->purchaseOrders()->find($id);

        if (!$order) {
            return $this->response->errorForbidden();
        }

        return $this->response->item($order, new PurchaseOrderTransformer($user));
    }

    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::user();

        $order = $user->purchaseOrders()->find($id);

        if (!$order) {
            return $this->response->errorForbidden();
        }

        $operate = $request->get('operate');

        $method = '_'.camel_case($operate);

        return $this->$method($order);
    }

    /**
     * @apiGroup Purchase_Order
     * @apiDescription 我的PO单取消
     *
     * @api {post} /purchase/orders/{id}--(cancle) PO单取消
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

    //contact不接受
    private function _contactCancel($order)
    {
        if ($order->status != 'IN_PROGRESS') {
            return $this->response->errorForbidden();
        }

        $order->status = 'CONTACT_CANCELED';
        $order->save();

        return $this->response->noContent();
    }

    //owner不接受
    private function _ownerCancel($order)
    {
        if ($order->status != 'CONFIRMED') {
            return $this->response->errorForbidden();
        }

        $order->status = 'OWNER_CANCELED';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup Purchase_Order
     * @apiDescription PO单联系人确认
     *
     * @api {post} /purchase/orders/{id}--(confirm) PO单联系人确认
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='confirm'} operate confirm
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function _confirm($order)
    {
        if (!$order->checkContact(\Auth::user()) || $order->status != 'IN_PROGRESS') {
            return $this->response->errorForbidden();
        }

        $order->status = 'CONFIRMED';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup Purchase_Order
     * @apiDescription PO单自己确认
     *
     * @api {post} /purchase/orders/{id}--(finish) PO单自己确认
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='finish'} operate finish
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function _finish($order)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'CONFIRMED') {
            return $this->response->errorForbidden();
        }

        $order->status = 'FINISHED';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup  Purchase_Order
     * @apiDescription PO单联系人回复
     *
     * @api {put} /purchase/orders/{id}--(reply) PO单联系人回复
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='reply'} operate  制造商回复
     * @apiParam {Integer} production_duration  生产时间
     * @apiParam {Number} production_price  生产价格
     * @apiParam {String} contact_note  制造商备注
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 ok
     */
    private function _reply($order)
    {
        if (!$order->checkContact(\Auth::user()) || $order->status != 'IN_PROGRESS') {
            return $this->response->errorForbidden();
        }

        $order->fill($request->get());

        $extra = $order->extra;
        $extra['contact_note'] = $request->get('contact_note') ?: null;

        $order->status = 'CONFIRMED';
        $order->extra = $extra;

        $order->save();

        return $this->response->noContent();
    }
}
