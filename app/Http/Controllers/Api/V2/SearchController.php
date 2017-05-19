<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Search\SearchRequest;
use App\Http\Requests\Api\Search\SearchAllRequest;
use App\Transformers\ServiceTransformer;
use App\Transformers\UserTransformer;
use App\Models\Service;
use App\Models\User;
use League\Fractal\Resource\Collection;
use League\Fractal\Manager;
use League\Fractal\Serializer\DataArraySerializer;

class SearchController extends BaseController
{
    /**
     * @apiGroup other
     * @apiDescription 补全搜索用户或服务
     *
     * @api {post} /search/autocomplete 补全搜索用户或服务
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String=service,maker,designer} type 搜索类型
     * @apiParam {String} name 关键字
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": [
     *     {
     *       "id": 50,
     *       "type": "MAKER",
     *       "cellphone": "",
     *       "email": "abcd@qq.com",
     *       "avatar": "http://wx.qlogo.cn/mmopen/PiajxSqBRaEIHec0RrdRrNnAumUqyMORaatMRX20MuJbIQpOCfK4picvEgss2guwO6MRKutHjw43XcziaQSWSQU8V7KO1ffGeA377AYSHfb0DQ/0",
     *       "first_name": "周",
     *       "last_name": "诚力",
     *       "gender": "MALE",
     *       "is_email_verified": 0,
     *       "is_cellphone_verified": 0,
     *       "created_at": "2015-09-01 08:25:01",
     *       "updated_at": "2016-03-17 03:37:58",
     *       "amount": "0.00",
     *       "status": "ACTIVE",
     *       "nickname": "Wolf制衣厂",
     *       "birthday": "2015-09-01 00:00:00",
     *       "privilege_amount": 0,
     *       "position": "",
     *       "is_verify": 1
     *     }
     *   ]
     * }
     */
    public function autocomplete(SearchRequest $request)
    {
        $type = $request->get('type');
        $name = $request->get('name');
        $limit = $request->get('per_page') ?: 5;
        switch ($type) {
            // 搜索服务的英文或中文名中已 xx开头的
            case 'service':
                $result = Service::where('status', 'ACTIVE')
                    ->where((app()->getLocale() == 'zh') ? 'name' : 'en_name', 'like', '%'.$name.'%')
                    ->whereHas('user', function ($query) {
                        $query->where('users.status', 'ACTIVE');
                    })->limit($limit)
                    ->get();

                return $this->collection($result, new ServiceTransformer());
                break;
            case 'designer':
                // 搜索用户的姓或名中已 xx开头的
                $result = User::where('status', 'ACTIVE')
                    ->where('is_verify', 1)
                    ->where('type', strtoupper($type))
                    ->where(function ($query) use ($name) {
                        $query->where('first_name', 'like', '%'.$name.'%')
                            ->orWhere('last_name', 'like', '%'.$name.'%')
                            ->orWhere('search_name', 'like', '%'.$name.'%');
                    })
                    ->limit($limit)
                    ->get();

                return $this->collection($result, new UserTransformer());
                break;
            case 'maker':
                // TODO 临时处理
                $factoryName = \App::getLocale() == 'zh' ? 'name' : 'en_name';
                // 搜索用户的姓或名中已 xx开头的
                $result = User::leftJoin('factories', 'factories.user_id', '=', 'users.id')
                    ->where('users.status', 'ACTIVE')
                    ->where('users.type', strtoupper($type))
                    ->where((app()->getLocale() == 'zh') ? 'factories.name' : 'factories.en_name', 'like', '%'.$name.'%')
                    ->select('users.*', 'factories.'.$factoryName.' as factory_name')
                    ->limit($limit)
                    ->get();

                return $this->collection($result, new UserTransformer());
                break;
            default:
                return $this->response->errorInternal();
                break;
        }
    }
    /**
     * @apiGroup other
     * @apiDescription 搜索设计师或制造商或服务
     *
     * @api {get} /search 搜索设计师或制造商或服务
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String} name 关键字
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": {
     *    "services": {
     *      "data": []
     *    },
     *    "makers": {
     *      "data": []
     *    },
     *    "designers": {
     *      "data": [
     *        {
     *          "id": 1,
     *          "type": "DESIGNER",
     *          "cellphone": "13880053334",
     *          "email": null,
     *          "avatar": "https://defara.imgix.net/userdata/assets/avatars/2015/09/02/d2dda957843dcca01599d8b02465741a09229913.jpg",
     *          "first_name": "牛",
     *          "last_name": "逼",
     *          "gender": "MALE",
     *          "is_email_verified": 0,
     *          "is_cellphone_verified": 1,
     *          "created_at": "2015-08-02 14:35:25",
     *          "updated_at": "2016-06-23 06:06:08",
     *          "status": "ACTIVE",
     *          "birthday": "2015-09-02 08:00:00",
     *          "is_verify": true,
     *          "logged_at": null,
     *          "account_name": null,
     *          "search_name": "牛逼",
     *          "is_favorite": false,
     *          "fullname": "牛 逼",
     *          "im_id": "02ba8dbef459e4130d7f3ccefd08a0c9",
     *          "has_password": true
     *        },
     *        {
     *          "id": 355,
     *          "type": "DESIGNER",
     *          "cellphone": "13698569874",
     *          "email": "32131121@qq.com",
     *          "avatar": "http://xiaodong.dev/assets/avatars/16/06/d7dbe9b2fc25eea132f2bf36a57a664c134180b8.jpg",
     *          "first_name": "牛yytssssgsfdgsdfgs",
     *          "last_name": "逼ssssfasdfasd",
     *          "gender": "MALE",
     *          "is_email_verified": 0,
     *          "is_cellphone_verified": 0,
     *          "created_at": "2016-04-12 12:50:48",
     *          "updated_at": "2016-06-28 09:58:28",
     *          "status": "ACTIVE",
     *          "birthday": "2016-06-15 00:00:00",
     *          "is_verify": true,
     *          "logged_at": "2016-05-31 17:08:47",
     *          "account_name": "王小",
     *          "search_name": "牛逼",
     *          "is_favorite": false,
     *          "fullname": "牛yytssssgsfdgsdfgs 逼ssssfasdfasd",
     *          "im_id": "d541acc00fb524339ac5dcd77313e296",
     *          "has_password": true
     *        }
     *      ]
     *    }
     *  }
     *}
     */
    public function search(SearchAllRequest $request)
    {
        $limit = $this->perPage ?: 3;
        $name = str_replace(' ', '', $request->get('name'));

        $services = Service::where('status', 'ACTIVE')
            ->where((app()->getLocale() == 'zh') ? 'name' : 'en_name', 'like', '%'.$name.'%')
            ->whereHas('user', function ($query) {
                $query->where('users.status', 'ACTIVE');
            })->limit($limit)
            ->get();

        $data['data']['services'] = $this->addData($services, new ServiceTransformer());

        $factoryName = \App::getLocale() == 'zh' ? 'name' : 'en_name';
        $makers = User::leftJoin('factories', 'factories.user_id', '=', 'users.id')
            ->where('users.status', 'ACTIVE')
            ->where('users.type', 'MAKER')
            ->where((app()->getLocale() == 'zh') ? 'factories.name' : 'factories.en_name', 'like', '%'.$name.'%')
            ->select('users.*', 'factories.'.$factoryName.' as factory_name')
            ->limit($limit)
            ->get();

        $data['data']['makers'] = $this->addData($makers, new UserTransformer());

        $designers = User::where('status', 'ACTIVE')
            ->where('is_verify', 1)
            ->where('type', 'DESIGNER')
            ->where(function ($query) use ($name) {
                $query->where('first_name', 'like', '%'.$name.'%')
                    ->orWhere('last_name', 'like', '%'.$name.'%');
            })
            ->limit($limit)
            ->get();

        $data['data']['designers'] = $this->addData($designers, new UserTransformer());

        return $data;
    }

    public function addData($data, $transformers)
    {
        $manager = new Manager();
        $manager->setSerializer(new DataArraySerializer());
        $resource = new Collection($data, $transformers);
        $datas = $manager->createData($resource)->toArray();

        return $datas;
    }
}
