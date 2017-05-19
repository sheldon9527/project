<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Models\Category;
use App\Models\Position;
use App\Models\Style;

class HomeController extends BaseController
{
    /**
     * @apiGroup others
     * @apiDescription  首页数据
     *
     * @api {get} /home/designers/filter 首页数据
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *     {
     *  "styles": [
     *    {
     *      "id": 1,
     *      "name": "简约"
     *    },
     *    {
     *      "id": 2,
     *      "name": "复古"
     *    },
     *    {
     *      "id": 3,
     *      "name": "中性"
     *    },
     *    {
     *      "id": 4,
     *      "name": "甜美"
     *    },
     *    {
     *      "id": 5,
     *      "name": "运动"
     *    },
     *  ],
     *  "positions": [
     *    {
     *      "id": 1,
     *      "name": "设计总监",
     *      "key": "creativeDirector"
     *    },
     *  ],
     *  "parentCategories": [
     *    {
     *      "id": 1,
     *      "parent_id": 0,
     *      "name": "女装",
     *      "icon_url": "http://xiaodong.dev/assets/categories/16/06/182780977057611421206b7.jpg"
     *    },
     *  ]
     *}
     */
    public function showFilterData()
    {
        $data = [];
        $styles = Style::all();
        $positions = Position::all();
        $parentCategories = Category::where('parent_id', 0)->get();
        $data['styles'] = $styles;
        $data['positions'] = $positions;
        $data['parentCategories'] = $parentCategories;

        return $data;
    }
}
