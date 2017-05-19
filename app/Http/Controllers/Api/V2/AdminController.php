<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\BaseTransformer;
use Illuminate\Http\Request;
use App\Models\Admin;

class AdminController extends BaseController
{
    /**
     * @apiGroup Others
     * @apiDescription 获取客服列表
     *
     * @api {get} /admins 获取客服列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "cellphone": "+8615397625376",
     *       "email": "123456@163.com",
     *       "username": "defara",
     *       "first_name": "周",
     *       "last_name": "光虎",
     *       "created_at": "2015-09-01 00:58:59",
     *       "updated_at": "2016-05-12 07:14:56",
     *       "status": "ACTIVE",
     *       "avatar": "http://local.defara/assets/default/defaultAvatar.jpg"
     *     }
     *   ]
     * }
     *}
     */
    public function index(Request $request)
    {
        $admins = Admin::all();

        return $this->response->collection($admins, new BaseTransformer());
    }
}
