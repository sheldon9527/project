<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\SampleOrderTransformer;
use Illuminate\Http\Request;

class SampleOrderController extends BaseController
{
    /**
     * @apiGroup  sample_orders
     * @apiDescription 打样单列表
     *
     * @api {get} /sample/orders 打样单列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='PENDING_PAY','IN_PROGRESS','UNSATISTY','SUBMITTED','DEFARA_SUBMITTED','EXPIRED','CANCELED','FINISHED'} status 按状态搜索,不区分大小写
     * @apiParam {Datetime} start_time 创建时间大于
     * @apiParam {Datetime} end_time 创建时间小于
     * @apiParam {String} keyword 名称或者id
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": [
     *    {
     *      "id": 1,
     *      "inquiry_order_id": 1,
     *      "owner_id": 9,
     *      "contact_id": 44,
     *      "name": "5235234542352345432523",
     *      "cover_picture_url": "https://defara.imgix.net/userdata/assets/profile/2015/09/01/a40b177065b34ee12a6fe6f29eb3ba142c48e12a.jpg",
     *      "style_no": "54352345234",
     *      "category_id": 1,
     *      "production_no": "1234555",
     *      "product_weight": "",
     *      "production_price": 0,
     *      "production_duration": 0,
     *      "note": "323",
     *      "submit_count": 1,
     *      "status": "FINISHED",
     *      "extra": null,
     *      "created_at": "2016-04-07 08:31:02",
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
     *          "updated_at": "2016-04-01 10:34:38",
     *          "status": "ACTIVE",
     *          "birthday": "2015-09-01 08:00:00",
     *          "is_verify": 1,
     *          "logged_at": null,
     *          "fullname": "王 和荣"
     *        }
     *      }
     *    }
     *  ],
     *  "meta": {
     *    "pagination": {
     *      "total": 4,
     *      "count": 1,
     *      "per_page": 1,
     *      "current_page": 1,
     *      "total_pages": 4,
     *      "links": {
     *        "next": "http://xiaodong.com/api/sample/orders?page=2"
     *      }
     *    }
     *  }
     *}
     */
    public function index(Request $request)
    {
        $user = \Auth::user();

        $orders = $user->sampleOrders();
        $status = strtoupper($request->get('status'));
        $allowStatuses = ['PENDING_PAY', 'IN_PROGRESS', 'DEFARA_SUBMITTED', 'EXPIRED', 'CANCELED', 'FINISHED', 'UNSATISTY'];

        // 状态搜索
        if (in_array($status, $allowStatuses)) {
            switch ($status) {
                case 'IN_PROGRESS':
                    if ($user->type != 'MAKER') {
                        $orders->where(function ($query) {
                            $query->orwhere('status', 'IN_PROGRESS')
                                ->orwhere('status', 'SUBMITTED');
                        });
                    } else {
                        $orders->where('status', $status);
                    }
                    break;
                case 'DEFARA_SUBMITTED':
                    if ($user->type == 'MAKER') {
                        $orders->where(function ($query) {
                            $query->orwhere('status', 'SUBMITTED')
                                ->orwhere('status', 'DEFARA_SUBMITTED');
                        });
                    } else {
                        $orders->where('status', $status);
                    }
                    break;
                default:
                    $orders->where('status', $status);
                break;
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
                    ->orWhere('owner_order_name', 'like', '%'.$keyword.'%')
                    ->orWhere('contact_order_name', 'like', '%'.$keyword.'%');
            });
        }

        $orders = $orders->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new SampleOrderTransformer($user));
    }

    /**
     * @apiGroup  sample_orders
     * @apiDescription 打样单详情
     *
     * @api {get} /sample/orders/{id} 打样单详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string='contract','comments'} [include]  包含的信息
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 ok
     *{
     *  "data": {
     *    "id": 4,
     *    "inquiry_order_id": 15,
     *    "owner_id": 9,
     *    "contact_id": 284,
     *    "name": "5235234542352345432523",
     *    "cover_picture_url": "https://defara.imgix.net/userdata/assets/profile/2015/09/01/a40b177065b34ee12a6fe6f29eb3ba142c48e12a.jpg",
     *    "style_no": "54352345234",
     *    "category_id": 1,
     *    "production_no": "1234555",
     *    "product_weight": "",
     *    "production_price": 999,
     *    "production_duration": 999,
     *    "note": "323",
     *    "submit_count": 3,
     *    "status": "SUBMITTED",
     *    "extra": null,
     *    "created_at": "2016-04-07 08:38:22",
     *    "deleted_at": null,
     *    "contact": {
     *      "data": {
     *        "id": 284,
     *        "type": "MAKER",
     *        "cellphone": "13868682328",
     *        "email": "",
     *        "avatar": "http://xiaodong.com/assets/default/defaultAvatar.jpg",
     *        "first_name": "金",
     *        "last_name": "春义",
     *        "gender": "MALE",
     *        "is_email_verified": 0,
     *        "is_cellphone_verified": 0,
     *        "created_at": "2015-12-28 16:19:13",
     *        "updated_at": "2016-04-01 10:34:22",
     *        "status": "ACTIVE",
     *        "birthday": "0000-00-00 00:00:00",
     *        "is_verify": 1,
     *        "logged_at": null,
     *        "fullname": "金 春义"
     *      }
     *    }
     *  }
     *}
     */
    public function show($id)
    {
        $user = \Auth::user();

        $order = $user->sampleOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($order, new SampleOrderTransformer($user));
    }
}
