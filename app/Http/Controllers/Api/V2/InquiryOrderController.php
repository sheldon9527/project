<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\InquiryOrderTransformer;
use App\Http\Requests\Api\InquiryOrder\StoreRequest;
use App\Http\Requests\Api\InquiryOrder\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\InquiryOrder;
use App\Models\User;

class InquiryOrderController extends BaseController
{
    /**
     * @apiGroup inquiry_orders
     * @apiDescription 我的询价单列表
     *
     * @api {get} /inquiry/orders 我的询价单列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} [include.owner]  自己
     * @apiParam {String='INQUIRING','EXPIRED','CANCELED','FINISHED'}} status 按状态搜索,不区分大小写
     * @apiParam {Datetime} start_time 创建时间大于
     * @apiParam {Datetime} end_time 创建时间小于
     * @apiParam {String} keyword 名称或者id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "owner_id": 355,
     *       "contact_id": 331,
     *       "name": "123",
     *       "cover_picture_url": "",
     *       "style_no": "",
     *       "category_id": 0,
     *       "product_weight": "",
     *       "production_no": "",
     *       "note": "",
     *       "status": "DRAFT",
     *       "extra": null,
     *       "created_at": "2015-01-01 00:00:01",
     *       "published_at": null,
     *       "deleted_at": null,
     *       "contact": {
     *         "data": {
     *           "id": 331,
     *           "type": "DESIGNER",
     *           "cellphone": null,
     *           "email": "keke@defara.com",
     *           "avatar": "http://local.defara/assets/default/defaultAvatar.jpg",
     *           "first_name": null,
     *           "last_name": null,
     *           "gender": null,
     *           "is_email_verified": 0,
     *           "is_cellphone_verified": 0,
     *           "created_at": "2016-02-16 02:40:38",
     *           "updated_at": "2016-04-01 02:34:22",
     *           "status": "INACTIVE",
     *           "birthday": null,
     *           "is_verify": 0,
     *           "logged_at": null,
     *           "fullname": null
     *         }
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "pagination": {
     *       "total": 1,
     *       "count": 1,
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

        $orders = $user->inquiryOrders();
        $status = strtoupper($request->get('status'));
        $allowStatuses = ['INQUIRING', 'EXPIRED', 'CANCELED', 'FINISHED'];
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
                    ->orwhere('name', 'like', '%'.$keyword.'%')
                    ->orwhere('en_name', 'like', '%'.$keyword.'%');
                if (!is_numeric($keyword)) {
                    $query->orwhere('inquiry_orders.id', (int) substr($keyword, 2));
                }
            });
        }

        $orders = $orders->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new InquiryOrderTransformer($user));
    }

    /**
     * @apiGroup inquiry_orders
     * @apiDescription 询价单详情
     *
     * @api {get} /inquiry/orders/{id} 询价单详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} [include]  可引入的关系
     * @apiParam {string} [include.owner]  自己
     * @apiParam {string} [include.comments]  留言
     * @apiParam {string} [include.questionnaire]  问卷星
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": {
     *     "id": 1,
     *     "owner_id": 355,
     *     "contact_id": 331,
     *     "name": "123",
     *     "cover_picture_url": "",
     *     "style_no": "",
     *     "category_id": 0,
     *     "product_weight": "",
     *     "production_no": "",
     *     "note": "",
     *     "status": "DRAFT",
     *     "extra": null,
     *     "created_at": "2015-01-01 00:00:01",
     *     "published_at": null,
     *     "deleted_at": null,
     *     "contact": {
     *       "data": {
     *         "id": 331,
     *         "type": "DESIGNER",
     *         "cellphone": null,
     *         "email": "keke@defara.com",
     *         "avatar": "http://local.defara/assets/default/defaultAvatar.jpg",
     *         "first_name": null,
     *         "last_name": null,
     *         "gender": null,
     *         "is_email_verified": 0,
     *         "is_cellphone_verified": 0,
     *         "created_at": "2016-02-16 02:40:38",
     *         "updated_at": "2016-04-01 02:34:22",
     *         "status": "INACTIVE",
     *         "birthday": null,
     *         "is_verify": 0,
     *         "logged_at": null,
     *         "fullname": null
     *       }
     *     }
     *   }
     * }
     */
    public function show($id, Request $request)
    {
        $user = \Auth::user();

        $order = $user->inquiryOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($order, new InquiryOrderTransformer($user));
    }
    // /**
    //  * @apiGroup  inquiry_orders
    //  * @apiDescription 创建询价单
    //  *
    //  * @api {post} /inquiry/orders 创建询价单
    //  * @apiVersion 0.2.0
    //  * @apiPermission jwt
    //  * @apiParam {String} [cover_picture_url]  订单封面图 tag= cover
    //  * @apiParam {String} name  订单名称
    //  * @apiParam {String} style_no  款号
    //  * @apiParam {Integer} category_id  订单类型
    //  * @apiParam {Integer} [contact_id]  制造商id
    //  * @apiParam {String} production_no  预计大货生产数量
    //  * @apiParam {String} [product_weight]  预计产品重量
    //  * @apiParam {Array}  attachments  设计资料 tag =detail
    //  * @apiParam {String} [note]  备注
    //  * @apiParam {Integer} address_id  订单地址
    //  * @apiParam {String=true} publish  是否发布
    //  * @apiSuccessExample {json} Success-Response:
    //  * HTTP/1.1 201 created
    //  */
    // public function store(StoreRequest $request)
    // {
    //     $user = \Auth::user();
    //     //创建订单
    //     $order = new InquiryOrder();
    //     $order->status = 'DRAFT';
    //
    //     if ($contactId = $request->get('contact_id')) {
    //         if ($contact = User::ofType('MAKER')->find($contactId)) {
    //             $order->contact_id = $contactId;
    //         }
    //     }
    //
    //     if ($request->get('publish') == true) {
    //         $order->publish();
    //     }
    //
    //     $order->fill($request->all());
    //     $coverPictureUrl = $request->get('cover_picture_url');
    //     $order->cover_picture_url = $coverPictureUrl ? parse_url($coverPictureUrl)['path'] : '';
    //
    //     $order->note = $request->get('note') ?: '';
    //     $order->owner_id = $user->id;
    //
    //     if ($productWeight = $request->get('product_weight')) {
    //         $order->product_weight = $productWeight;
    //     }
    //
    //     $order->production_no = $request->get('production_no');
    //
    //     $order->save();
    //
    //     //更新附件
    //     if ($attachments = $request->get('attachments')) {
    //         $order->updateAttachment($attachments, 'detail');
    //     }
    //
    //     $addressId = $request->get('address_id');
    //     $userAddress = $user->addresses()->find($addressId);
    //
    //     //创建订单地址
    //     $order->updateAddress($userAddress->toArray());
    //
    //     return $this->item($order, new InquiryOrderTransformer($user));
    // }

    /**
     * @apiGroup inquiry_Orders
     * @apiDescription 询价单修改
     *
     * @api {put} /inquiry/orders/{id} 询价单修改
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='cancel','finish'} operate  询价单修改
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::user();
        $order = $user->inquiryOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }
        $operate = $request->get('operate');

        $method = camel_case('update_'.$operate);

        return $this->$method($order);
    }

    /**
     * @apiGroup inquiry_orders
     * @apiDescription 取消询价单
     *
     * @api {put} /inquiry/orders/{id}--(cancel) 取消询价单
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='cancel'} operate 操作为cancel
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function updateCancel($order)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'INQUIRING') {
            return $this->response->errorForbidden();
        }

        $order->status = 'CANCELED';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiry_orders
     * @apiDescription 询价单确认制造商
     *
     * @api {put} /inquiry/orders/{id}--(finish) 询价单确认制造商
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='cancel'} operate 操作为cancel
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function updateFinish($order)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'INQUIRING') {
            return $this->response->errorForbidden();
        }

        $order->status = 'FINISHED';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiry_orders
     * @apiDescription 删除询价单
     *
     * @api {delete} /inquiry/orders/{id} 删除询价单
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    public function destroy($id)
    {
        $user = \Auth::user();

        $order = $user->inquiryOrders()->find($id);
        if (!$order) {
            return $this->response->errorNotFound();
        }

        $order->delete();

        return $this->response->noContent();
    }
}
