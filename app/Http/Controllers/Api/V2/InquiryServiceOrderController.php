<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\InquiryServiceOrder\UpdateRequest;
use App\Transformers\InquiryServiceOrderTransformer;
use Illuminate\Http\Request;

class InquiryServiceOrderController extends BaseController
{
    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 我的设计询价服务列表
     *
     * @api {get} /inquiry_service/orders 我的设计询价服务列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='INQUIRING','MATCHING','PENDING_PAY','PAID','DRAFT_APPROVED','FINAL_PROGRESS','FINAL_APPROVED','CANCELED','FINISHED'}}status 按状态搜索,不区分大小写'MATCHING' => '询价中','PENDING_PAY' => '待付款','PAID' => '初稿设计中','DRAFT_APPROVED' => '已提交初稿','FINAL_PROGRESS' => '终稿设计中','FINAL_APPROVED' => '已提交终稿','CANCELED' => '已删除','FINISHED' => '已完成',
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
     *      "contract_id": 0,
     *      "questionnaire_item_id": 1,
     *      "title": "坎坎坷坷",
     *      "en_title": "dsfasd",
     *      "amount": "100.00",
     *      "status": "MATCHING",
     *      "created_at": "2016-07-18 17:33:24",
     *      "updated_at": "2016-07-18 17:33:24",
     *      "statusLabel": "询价中",
     *      "order_no": "IS000001",
     *      "owner": {
     *        "data": {
     *          "id": 359,
     *          "type": "DEALER",
     *          "cellphone": "13985698548",
     *          "email": "dealer1@qq.com",
     *          "avatar": "/assets/avatars/2016/07/967609655786cfa0117caf99727fc4e4976b5e62.png",
     *          "first_name": "dealer1@qq.com",
     *          "last_name": "123456",
     *          "gender": "MALE",
     *          "is_email_verified": 0,
     *          "is_cellphone_verified": 0,
     *          "created_at": "2016-04-12 12:50:48",
     *          "updated_at": "2016-07-18 09:33:27",
     *          "status": "ACTIVE",
     *          "birthday": null,
     *          "is_verify": true,
     *          "logged_at": "2016-07-18 09:33:27",
     *          "account_name": "0",
     *          "search_name": "dsfdsafas42424243",
     *          "is_favorite": false,
     *          "fullname": "dealer1@qq.com 123456",
     *          "im_id": "49240c8d2e283e8d4894799354da6749",
     *          "has_password": true
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

        $orders = $user->inquiryServiceOrders();
        if ($user->type == 'DESIGNER') {
            $orders->where('status', '!=', 'INQUIRING')->where('status', '!=', 'MATCHING');
        }
        $status = strtoupper($request->get('status'));

        $allowStatuses = [
            'INQUIRING',
            'MATCHING',
            'PENDING_PAY',
            'PAID',
            'DRAFT_APPROVED',
            'FINAL_PROGRESS',
            'FINAL_APPROVED',
            'CANCELED',
            'FINISHED',
            'EXPIRED',
        ];
        // 状态搜索
        if (in_array($status, $allowStatuses)) {
            switch ($status) {
                case 'PAID':
                    if ($user->type != 'DESIGNER') {
                        $orders->where(function ($query) use ($status) {
                            $query->orwhere('status', 'DRAFT_PENGIND_APPROVAL')
                                ->orwhere('status', 'PAID');
                        });
                    } else {
                        $orders->where('status', $status);
                    }
                    break;
                case 'DRAFT_APPROVED':
                    if ($user->type == 'DESIGNER') {
                        $orders->where(function ($query) use ($status) {
                            $query->orwhere('status', 'DRAFT_APPROVED')
                                ->orwhere('status', 'DRAFT_PENGIND_APPROVAL');
                        });
                    } else {
                        $orders->where('status', $status);
                    }
                    break;
                case 'FINAL_PROGRESS':
                    if ($user->type != 'DESIGNER') {
                        $orders->where(function ($query) use ($status) {
                            $query->orwhere('status', 'FINAL_PROGRESS')
                                ->orwhere('status', 'FINAL_PENGIND_APPROVAL');
                        });
                    } else {
                        $orders->where('status', $status);
                    }
                    break;
                case 'FINAL_APPROVED':
                    if ($user->type == 'DESIGNER') {
                        $orders->where(function ($query) use ($status) {
                            $query->orwhere('status', 'FINAL_APPROVED')
                                ->orwhere('status', 'FINAL_PENGIND_APPROVAL');
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
                    ->orWhere('title', 'like', '%'.$keyword.'%')
                    ->orWhere('en_title', 'like', '%'.$keyword.'%');
                if (!is_numeric($keyword)) {
                    $query->orwhere('inquiry_orders.id', (int) substr($keyword, 2));
                }
            });
        }

        $orders = $orders->orderBy('updated_at', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($orders, new InquiryServiceOrderTransformer($user));
    }

    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 我的设计询价服务详情
     *
     * @api {get} /inquiry_service/orders/{id} 我的设计询价服务详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} [include=fristDrafts,finalDrafts,comments,contract,questionnaire]  可引入的关系
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *     {
     *  "data": {
     *    "id": 1,
     *    "owner_id": 359,
     *    "contract_id": 0,
     *    "contact_id": 0,
     *    "questionnaire_item_id": 1,
     *    "title": "坎坎坷坷",
     *    "amount": "100.00",
     *    "status": "MATCHING",
     *    "created_at": "2016-07-18 17:33:24",
     *    "updated_at": "2016-07-18 17:33:24",
     *    "statusLabel": "询价中",
     *    "order_no": "IS000001",
     *    "owner": {
     *      "data": {
     *        "id": 359,
     *        "type": "DEALER",
     *        "cellphone": "13985698548",
     *        "email": "dealer1@qq.com",
     *        "avatar": "/assets/avatars/2016/07/967609655786cfa0117caf99727fc4e4976b5e62.png",
     *        "first_name": "dealer1@qq.com",
     *        "last_name": "123456",
     *        "gender": "MALE",
     *        "is_email_verified": 0,
     *        "is_cellphone_verified": 0,
     *        "created_at": "2016-04-12 12:50:48",
     *        "updated_at": "2016-07-18 09:33:27",
     *        "status": "ACTIVE",
     *        "birthday": null,
     *        "is_verify": true,
     *        "logged_at": "2016-07-18 09:33:27",
     *        "account_name": "0",
     *        "search_name": "dsfdsafas42424243",
     *        "is_favorite": false,
     *        "fullname": "dealer1@qq.com 123456",
     *        "im_id": "49240c8d2e283e8d4894799354da6749",
     *        "has_password": true
     *      }
     *    },
     *    "fristDrafts": {
     *      "data": [
     *        {
     *          "id": 5073,
     *          "user_id": 355,
     *          "relative_path": "/assets/inquiry_services/16/06/15961834165767ac8479b91.png",
     *          "filename": "8e47d1a758406679298cd100eb6850d1.png",
     *          "description": null,
     *          "tag": "first",
     *          "width": 512,
     *          "height": 512,
     *          "mime_types": "image/png",
     *          "weight": 0,
     *          "created_at": "2016-06-20 08:42:44",
     *          "updated_at": "2016-07-05 08:00:41"
     *        }
     *      ]
     *    },
     *    "finalDrafts": {
     *      "data": [
     *        {
     *          "id": 5085,
     *          "user_id": 355,
     *          "relative_path": "/assets/inquiry_services/16/06/56494038657724a608a09f.png",
     *          "filename": "0e8e9f4b60df775dd64f07b68dfbebdf.png",
     *          "description": null,
     *          "tag": "final",
     *          "width": 1024,
     *          "height": 683,
     *          "mime_types": "image/png",
     *          "weight": 0,
     *          "created_at": "2016-06-28 09:58:56",
     *          "updated_at": "2016-07-04 07:40:43"
     *        }
     *      ]
     *    },
     *    "comments": {
     *      "data": [
     *        {
     *          "id": 2,
     *          "user_id": 359,
     *          "admin_id": 1,
     *          "content": "的发生的发生发送到发送到发送到发送到发送到发送到发送到法是",
     *          "created_at": "2016-07-19 12:33:15",
     *          "updated_at": "2016-07-19 12:33:15",
     *          "user": {
     *            "data": {
     *              "id": 359,
     *              "type": "DEALER",
     *              "cellphone": "13985698548",
     *              "email": "dealer1@qq.com",
     *              "avatar": "/assets/avatars/2016/07/967609655786cfa0117caf99727fc4e4976b5e62.png",
     *              "first_name": "dealer1@qq.com",
     *              "last_name": "123456",
     *              "gender": "MALE",
     *              "is_email_verified": 0,
     *              "is_cellphone_verified": 0,
     *              "created_at": "2016-04-12 12:50:48",
     *              "updated_at": "2016-07-18 09:33:27",
     *              "status": "ACTIVE",
     *              "birthday": null,
     *              "is_verify": true,
     *              "logged_at": "2016-07-18 09:33:27",
     *              "account_name": "0",
     *              "search_name": "dsfdsafas42424243",
     *              "is_favorite": false,
     *              "fullname": "dealer1@qq.com 123456",
     *              "im_id": "49240c8d2e283e8d4894799354da6749",
     *              "has_password": true
     *            }
     *          }
     *        }
     *      ]
     *    },
     *    "questionnaire": {
     *      "data": {
     *        "id": 3,
     *        "questionnaire_id": 1,
     *        "user_id": 651,
     *        "index": 3,
     *        "answer": [
     *          {
     *            "topic": "你打算生产哪个品类的时尚产品？ ",
     *            "answer": "女装-T恤&衬衣"
     *          },
     *          {
     *            "topic": "请上传一张产品图片",
     *            "answer": "http://files.sojump.com/8941016_q2_9bSMWgr92kWMkDOAuvmE2Q"
     *          },
     *          {
     *            "topic": "产品数量预计在哪个范围？",
     *            "answer": "单件定制"
     *          },
     *          {
     *            "topic": "你希望工厂在什么时间之内完成生产？",
     *            "answer": "25个工作日内"
     *          },
     *          {
     *            "topic": "你能为工厂提供产品设计资料吗？",
     *            "answer": "设计图稿"
     *          },
     *          {
     *            "topic": "设计图稿上传",
     *            "answer": "http://files.sojump.com/8941016_q6_jA8H9Lv1skKPravq2ZY2LQ"
     *          },
     *          {
     *            "topic": "工艺单（Teck Pack）上传",
     *            "answer": "http://files.sojump.com/8941016_q7_qW8rvCv45kqKakzbfGTG8w"
     *          },
     *          {
     *            "topic": "你能提供产品实物资料吗？",
     *            "answer": "面料实物--服饰（或鞋包）原样"
     *          },
     *          {
     *            "topic": "你期望谁提供面料？",
     *            "answer": "自己采购并寄送"
     *          },
     *          {
     *            "topic": "你期望谁提供辅料？",
     *            "answer": "自己寄送"
     *          },
     *          {
     *            "topic": "生产完成的大货寄往哪里？",
     *            "answer": "大货地址"
     *          },
     *          {
     *            "topic": "备注",
     *            "answer": "备注"
     *          }
     *        ],
     *        "created_at": "2016-07-19 05:39:52",
     *        "updated_at": "2016-07-19 05:39:52"
     *      }
     *    }
     *  }
     *}
     */
    public function show($id)
    {
        $user = \Auth::user();

        $order = $user->inquiryServiceOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($order, new InquiryServiceOrderTransformer($user));
    }
    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 我的设计询价服务修改
     *
     * @api {put} /inquiry_service/orders/{id} 我的设计询价服务修改
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='match','match_cancel','draft_update','final_update','draft_submit','draft_confirm','draft_cancel','fanal_submit','accept'} operate  我的设计询价服务修改操作
     * @apiParam {Array} [attachments]  委托设计订单提供图片 tag =first 初稿, final 终稿
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::user();

        $order = $user->inquiryServiceOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        $operate = $request->get('operate');

        $method = camel_case($operate);

        return $this->$method($order, $request);
    }

    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 匹配中
     *
     * @api {put} /inquiry_service/orders/{id}--(match) 确认匹配
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function match($order, $request)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'INQUIRING') {
            return $this->response->errorForbidden();
        }

        $order->status = 'MATCHING';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 匹配取消
     *
     * @api {put} /inquiry_service/orders/{id}--(matchCancel) 匹配取消
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function matchCancel($order, $request)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'INQUIRING') {
            return $this->response->errorForbidden();
        }

        $order->status = 'CANCELED';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 设计师提交初稿
     *
     * @api {put} /inquiry_service/orders/{id}--(draftSubmit) 提交初稿
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function draftSubmit($order, $request)
    {
        if (!$order->checkContact(\Auth::user()) || !in_array($order->status, ['PAID', 'DRAFT_PENGIND_APPROVAL', 'DRAFT_APPROVED'])) {
            return $this->response->errorForbidden();
        }

        $order->status = 'DRAFT_PENGIND_APPROVAL';
        $order->save();

        if ($attachments = $request->get('attachments')) {
            $order->updateAttachment($attachments, 'first');
        }

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 小B或制造商接受
     *
     * @api {put} /inquiry_service/orders/{id}--(draftConfirm) 确认初稿
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function draftConfirm($order, $request)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'DRAFT_APPROVED') {
            return $this->response->errorForbidden();
        }

        $order->status = 'FINAL_PROGRESS';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 小B或制造商修改
     *
     * @api {put} /inquiry_service/orders/{id}--(draftUpdate) 初稿修改
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function draftUpdate($order, $request)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'DRAFT_APPROVED') {
            return $this->response->errorForbidden();
        }

        $order->status = 'PAID';
        $order->save();

        return $this->response->noContent();
    }
    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 小B或制造商取消
     *
     * @api {put} /inquiry_service/orders/{id}--(draftCancel) 用户取消
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function draftCancel($order, $request)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'DRAFT_APPROVED') {
            return $this->response->errorForbidden();
        }
        \DB::beginTransaction();
        try {
            $order->status = 'CANCELED';

            $order->refund()->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 设计师接受
     *
     * @api {put} /inquiry_service/orders/{id}--(fanalSubmit) 提交终稿
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function fanalSubmit($order, $request)
    {
        if (!$order->checkContact(\Auth::user()) || !in_array($order->status, ['FINAL_PROGRESS', 'FINAL_PENGIND_APPROVAL', 'FINAL_APPROVED'])) {
            return $this->response->errorForbidden();
        }

        $order->status = 'FINAL_PENGIND_APPROVAL';
        $order->save();

        if ($attachments = $request->get('attachments')) {
            $order->updateAttachment($attachments, 'final');
        }

        return $this->response->noContent();
    }

    /**
     * @apiGroup inquiry_service_Orders
     * @apiDescription 设计师接受
     *
     * @api {put} /inquiry_service/orders/{id}--(accept) 订单完成
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function accept($order, $request)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'FINAL_APPROVED') {
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
     * @apiGroup inquiry_service_Orders
     * @apiDescription 小B或制造商终稿修改
     *
     * @api {put} /inquiry_service/orders/{id}--(finalUpdate) 终稿修改
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function finalUpdate($order, $request)
    {
        if (!$order->checkOwner(\Auth::user()) || $order->status != 'FINAL_APPROVED') {
            return $this->response->errorForbidden();
        }

        $order->status = 'FINAL_PROGRESS';
        $order->save();

        return $this->response->noContent();
    }
}
