<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Models\Bank;
use App\Transformers\BankTransformer;

class BankController extends BaseController
{
    /**
     * @apiGroup others
     * @apiDescription  银行列表
     *
     * @api {get} /banks 银行列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 1209,
     *       "name": "招商银行"
     *     },
     *     {
     *       "id": 1210,
     *       "name": "中国银行"
     *     },
     *     {
     *       "id": 1211,
     *       "name": "交通银行"
     *     },
     *     {
     *       "id": 1212,
     *       "name": "中信银行"
     *     },
     *   ]
     * }
     */
    public function index()
    {
        $banks = Bank::all();

        return $this->response->collection($banks, new BankTransformer());
    }
}
