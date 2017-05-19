<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\TransactionTransformer;
use App\Http\Requests\Api\Transaction\UserIndexRequest;
use App\Models\Transaction;
use App\Models\User;

class TransactionController extends BaseController
{
    /**
     * @apiGroup transaction
     * @apiDescription 当前用户的资金流水列表
     *
     * @api {get} /user/transactions 当前用户的资金流水列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {datetime} [start_time]  开始时间
     * @apiParam {datetime} [end_time]  结束时间
     * @apiParam {string} [perPage]  可选分页
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *  "data": [
     *      {
     *          "id": 1,
     *          "reference_id": "2015090121001004030049431558",
     *          "user_id": 49,
     *          "amount": "0.10",
     *          "note": null,
     *          "created_at": "2015-09-01 07:39:59",
     *          "type": "ALIPAY",
     *          "pay_account": "15208267703",
     *          "updated_at": null
     *      },
     *      {
     *          "id": 2,
     *          "reference_id": "2015090121001004030040353220",
     *          "user_id": 49,
     *          "amount": "0.01",
     *          "note": null,
     *          "created_at": "2015-09-01 09:08:04",
     *          "type": "ALIPAY",
     *          "pay_account": "15208267703",
     *          "updated_at": null
     *      },
     *      {
     *          "id": 4,
     *          "reference_id": null,
     *          "user_id": 49,
     *          "amount": "10.00",
     *          "note": null,
     *          "created_at": "2015-09-30 05:25:40",
     *          "type": "DEFARA",
     *          "pay_account": "",
     *          "updated_at": null
     *      }
     *  ],
     *  "meta": {
     *      "pagination": {
     *          "total": 3,
     *          "count": 3,
     *          "per_page": 20,
     *          "current_page": 1,
     *          "total_pages": 1,
     *          "links": []
     *      }
     *  }
     * }
     */
    public function userIndex(UserIndexRequest $request)
    {
        $user = \Auth::User();

        //开始时间
        //TODO 暂时这样处理，显示出来source 为amount的明细
        $transactions = $user->transactions()->where('source', 'amount');

        if ($start_time = $request->get('start_time')) {
            $transactions->where('created_at', '>=', $start_time);
        }
        //结束时间
        if ($end_time = $request->get('end_time')) {
            $transactions->where('created_at', '<=', $end_time);
        }

        $transactions = $transactions->orderBy('id', 'desc')->paginate($request->get('per_page'));

        return $this->response->paginator($transactions, new TransactionTransformer());
    }
}
