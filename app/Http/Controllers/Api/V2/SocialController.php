<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Models\Social;
use App\Transformers\BaseTransformer;

class SocialController extends BaseController
{
    /**
     * @apiGroup Auth
     * @apiDescription 社交账号列表
     *
     * @api {get} /socials 社交账号列表
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "weixin"
     *     },
     *     {
     *       "id": 2,
     *       "name": "weibo"
     *     },
     *     {
     *       "id": 3,
     *       "name": "twitter"
     *     },
     *     {
     *       "id": 4,
     *       "name": "facebook"
     *     },
     *     {
     *       "id": 5,
     *       "name": "google"
     *     },
     *     {
     *       "id": 6,
     *       "name": "qq"
     *     },
     *     {
     *       "id": 7,
     *       "name": "pick"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $socials = Social::all();

        return $this->response->collection($socials, new BaseTransformer());
    }
}
