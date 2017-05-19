<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\ContractTransformer;
use App\Http\Requests\Api\Contract\ShowRequest;
use App\Http\Requests\Api\Contract\PreviewRequest;
use App\Models\Contract;
use App\Models\Order;

class ContractController extends BaseController
{
    /**
     * @apiGroup Contract
     * @apiDescription 合同预览
     *
     * @api {post} /contracts/preview 合同预览
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string=service,inquiry,sample,purchase,production} type 合同类型
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     */
    public function preview(PreviewRequest $request)
    {
        $type = strtoupper($request->get('type'));
        $contract = Contract::where('type', $type)->orderBy('version', 'desc')->first();

        if (!$contract) {
            return $this->response->errorNotFound();
        }

        $contract->changeContent($request->input());

        return $this->response->item($contract, new ContractTransformer());
    }

    /**
     * @apiGroup Contract
     * @apiDescription 订单合同查看
     *
     * @api {get} /order/contract 订单合同查看
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} order_no 订单编号
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": {
     *     "id": 17,
     *     "name": "",
     *     "content": "foobar合同内容",
     *     "version": 14,
     *     "params": [
     *       "name",
     *       "service_description",
     *       "description",
     *       "amount",
     *       "owner_name",
     *       "contact_name",
     *       "service_type",
     *       "attachments"
     *     ],
     *     "type": "SERVICE",
     *     "created_at": "2016-05-17 04:03:11",
     *     "updated_at": "2016-05-17 04:03:11"
     *   }
     * }
     */
    public function orderShow(ShowRequest $request)
    {
        $user = $this->user();
        $orderNo = $request->get('order_no');
        $order = Order::findByNo($orderNo);

        if (!$order || ($order->checkOwner($user) && $order->checkContact($user))) {
            return $this->response->errorNotFound();
        }

        $contract = $order->contract;

        if (!$contract) {
            return $this->response->errorNotFound();
        }

        $contract->changeContent($order->getContractParams());

        return $this->response->item($contract, new ContractTransformer());
    }
}
