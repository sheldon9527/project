<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\CategoryTransformer;
use App\Models\Category;
use App\Models\ServiceResult;

class CategoryController extends BaseController
{
    /**
     * @apiGroup others
     * @apiDescription 分类列表
     *
     * @api {get} /categories 分类列表
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String} [include] 可引入的关系
     * @apiParam {String} [include.children] 主分类的子类
     * @apiSuccessExample {json} Success-Response 全部分类及主分类:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *  {
     *    "id": 1,
     *    "parent_id": 0,
     *    "name": "男装",
     *    "children": {
     *      "data": [
     *        {
     *          "id": 2,
     *          "parent_id": 1,
     *          "name": "男士西服套装"
     *        },
     *        {
     *          "id": 3,
     *          "parent_id": 1,
     *          "name": "男士衬衣"
     *        },
     *        {
     *          "id": 4,
     *          "parent_id": 1,
     *          "name": "男士T恤/马球衫"
     *        },
     *        {
     *          "id": 5,
     *          "parent_id": 1,
     *          "name": "外套/夹克"
     *        },
     *        {
     *          "id": 6,
     *          "parent_id": 1,
     *          "name": "裤子"
     *        },
     *        {
     *          "id": 7,
     *          "parent_id": 1,
     *          "name": "带帽衫/卫衣"
     *        },
     *        {
     *          "id": 8,
     *          "parent_id": 1,
     *          "name": "毛衣"
     *        },
     *        {
     *          "id": 9,
     *          "parent_id": 1,
     *          "name": "运动装"
     *        },
     *        {
     *          "id": 10,
     *          "parent_id": 1,
     *          "name": "内衣物"
     *        },
     *        {
     *          "id": 11,
     *          "parent_id": 1,
     *          "name": "泳装"
     *        }
     *      ]
     *    }
     *  }
     * ]
     *}
     */
    public function index()
    {
        $categories = Category::where('parent_id', 0)->get();

        $results = ServiceResult::all();
        return $this->response->collection($categories, new CategoryTransformer())
            ->addMeta('results', $results);
    }
}
