<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\User\UpdateRequest;
use App\Http\Requests\Api\User\TempInfoRequest;
use App\Http\Requests\Api\User\UpdatePasswordRequest;
use App\Http\Requests\Api\User\UpdateDealerProfileRequest;
use Illuminate\Http\Request;
use App\Transformers\DesignerTransformer;
use App\Transformers\MakerTransformer;
use App\Transformers\UserAuthenticationTransformer;
use App\Transformers\UserTransformer;
use App\Models\Category;
use App\Models\User;
use App\Models\Attachment;
use Carbon\Carbon;
use App\Models\Style;
use App\Models\ServiceResult;
use App\Models\Position;

class UserController extends BaseController
{
    /**
     * @apiGroup User
     * @apiDescription 获取用户信息
     *
     * @api {get} /user 获取用户信息
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='true'}　[show_amount]  获取金额
     * @apiParam {String='true'}　[show_im_token]  获取im token
     * @apiParam {String='true'}　[show_order_message]  获取订单消息个数
     * @apiParam {String='true'}　[show_notifications_count]  获取系统消息个数
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200
     *{
     *  "data": {
     *    "id": 359,
     *    "type": "DEALER",
     *    "cellphone": null,
     *    "email": "dealer1@qq.com",
     *    "avatar": "http://xiaodong.com/assets/default/defaultAvatar.jpg",
     *    "first_name": null,
     *    "last_name": null,
     *    "gender": "SECRET",
     *    "is_email_verified": 0,
     *    "is_cellphone_verified": 0,
     *    "created_at": "2016-04-12 12:50:48",
     *    "updated_at": "2016-04-19 07:51:44",
     *    "status": "ACTIVE",
     *    "birthday": null,
     *    "is_verify": 1,
     *    "logged_at": "2016-04-19 03:46:07",
     *    "account_name": "易晓东",
     *    "fullname": "dealer1@qq.com"
     *  },
     *  "meta": {
     *    "amount": "222.00"
     *  }
     *}
     */
    public function userShow(Request $request)
    {
        $user = \Auth::user();

        switch ($user->type) {
            case 'DESIGNER':
                $transformer = new DesignerTransformer();
                break;
            case 'MAKER':
                $transformer = new MakerTransformer();
                break;
            case 'DEALER':
                $transformer = new UserTransformer();
                break;
        }

        $response = $this->response->item($user, $transformer);

        if ($request->get('show_amount') == 'true') {
            $response->addMeta('amount', $user->amount);
        }

        if ($request->get('show_im_token') == 'true') {
            $response->addMeta('im_token', $user->im_token);
        }

        if ($request->get('show_order_message') == 'true') {
            $response->addMeta('order_message_count', $user->orderMessages()->count());
        }

        if ($request->get('show_notifications_count') == 'true') {
            $response->addMeta('show_notifications_count', $user->notifications()
                ->where('notifications.is_read', 0)->count());
        }

        $response->addMeta('im_token', $user->im_token);

        return $response;
    }

    /**
     * @apiGroup User
     * @apiDescription 修改密码
     *
     * @api {post} /user/password 修改密码
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String{6..20}} old_password 旧密码
     * @apiParam {String{6..20}} password 新密码
     * @apiParam {String{6..20}} password_confirmation 确认密码
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = \Auth::user();

        if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            //email
            $credentials = [
                'email' => $user->email,
                'password' => $request->get('old_password'),
            ];
        } else {
            //phone
            $credentials = [
                'cellphone' => $user->cellphone,
                'password' => $request->get('old_password'),
            ];
        }

        //验证旧密码   非第三方用户才需要验证密码
        if ($user->password) {
            if (!\Auth::validate($credentials)) {
                return response()->json(['errors' => ['old_password' => trans('error.auth.invalid_old_password')]], 400);
            }
        }

        $user->password = bcrypt($request->get('password'));
        $user->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup User
     * @apiDescription 修改用户详情
     *
     * @api {post} /user (设计师) 修改用户详情
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} first_name 姓 用户不为制造商时必填
     * @apiParam {String} last_name 名 用户不为制造商时必填
     * @apiParam {String} gender 性别 用户不为制造商时必填
     * @apiParam {Url} avatar_url 头像url,支持直接传头像url过来
     * @apiParam {String} avatar 和avatar_url二者必有一个，用户头像base64
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": {
     *     "id": 355,
     *     "type": "DEALER",
     *     "cellphone": "13281898731",
     *     "email": null,
     *     "avatar": "http://local.defara/assets/avatars/2016/03/31/f43480151f7e89f3c77d09ca9ad0a13b8bcbeeac.jpg",
     *     "first_name": "李a",
     *     "last_name": "煜a",
     *     "gender": "FEMALE",
     *     "is_email_verified": 0,
     *     "is_cellphone_verified": 1,
     *     "created_at": "2016-03-30 06:30:22",
     *     "updated_at": "2016-03-31 07:53:33",
     *     "amount": "0.00",
     *     "status": "INACTIVE",
     *     "birthday": null,
     *     "privilege_amount": 0,
     *     "position": "",
     *     "is_verify": 1,
     *     "fullname": "李a 煜a"
     *   }
     * }
     */
    public function update(UpdateRequest $request)
    {
        $user = \Auth::user();

        if ($avatar = $request->file('avatar')) {
            $destinationPath = 'assets/avatars/'.date('Y/m/');
            $extension = $avatar->getClientOriginalExtension();
            $fileName = hash('ripemd160', time().rand(1000000, 99999999)).'.'.$extension;

            list($width, $height) = getimagesize($avatar);

            $avatarPath = (string) $avatar->move($destinationPath, $fileName);

            $user->avatar = $avatarPath;
            if ($url = Attachment::syncFile($avatarPath)) {
                $user->avatar = $url;
            }
        } elseif ($avatar = $request->get('avatar')) {
            //TODO 写的有问题
            $avatar = \Image::make($avatar);

            $fileName = $user->id.'-avatar-'.uniqid().'.png';

            $avatarPath = 'assets/avatars/'.date('y/m/').$fileName;
            \File::makeDirectory('assets/avatars/'.date('y/m/'), $mode = 0755, true, true);

            $avatar->save($avatarPath);

            $user->avatar = $avatarPath;
            if ($url = Attachment::syncFile($avatarPath)) {
                $user->avatar = $url;
            }
        } else {
            $user->avatar = $request->get('avatar_url');
        }

        $user->fill($request->only(['first_name', 'last_name', 'gender']));
        $user->search_name = trim($request->get('first_name')).trim($request->get('last_name'));
        $user->save();

        return $this->response->item($user, new UserTransformer());
    }

    public function updateProfile(UpdateDealerProfileRequest $request)
    {
        $user = \Auth::user();
        if ($user->type == 'DEALER') {
            // 更新经销商信息
            return $this->_dealerUpdate($request);
        }

        return $this->response->errorForbidden();
    }

    /**
     * @apiGroup User
     * @apiDescription 修改用户详情-小b
     *
     * @api {post} /user/profile(小b) 修改用户详情-小b
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {string} first_name 姓
     * @apiParam {String} last_name 名
     * @apiParam {String} gender 性别
     * @apiParam {String} description 个人描述
     * @apiParam {Url} [avatar_url] 头像url,支持直接传头像url过来
     * @apiParam {File} avatar 和avatar_url二着必有一个
     * @apiParam {String} [qq] qq号
     * @apiParam {String} [weixin] 微信
     * @apiParam {Email} [email] 邮箱
     * @apiParam {String} [cellphone] 电话号码
     * @apiParam {Array} want_do 希望进行，数组-[buy,sale,production]
     * @apiParam {Array} category_ids 分类id
     * @apiParam {Array} [shop_urls] 店铺地址 数组
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": {
     *     "id": 355,
     *     "type": "DEALER",
     *     "cellphone": "13281898731",
     *     "email": null,
     *     "avatar": "http://local.defara/assets/avatars/2016/03/31/f43480151f7e89f3c77d09ca9ad0a13b8bcbeeac.jpg",
     *     "first_name": "李a",
     *     "last_name": "煜a",
     *     "gender": "FEMALE",
     *     "is_email_verified": 0,
     *     "is_cellphone_verified": 1,
     *     "created_at": "2016-03-30 06:30:22",
     *     "updated_at": "2016-03-31 07:53:33",
     *     "amount": "0.00",
     *     "status": "INACTIVE",
     *     "birthday": null,
     *     "privilege_amount": 0,
     *     "position": "",
     *     "is_verify": 1,
     *     "fullname": "李a 煜a"
     *   }
     * }
     */
    public function _dealerUpdate($request)
    {
        //判断主分类个数
        $categoryIds = $request->get('category_ids');
        $categoryIds = $categoryIds ? array_filter(array_map('intval', $categoryIds)) : [];
        $rootNUmber = Category::whereIn('id', $categoryIds)->groupBy('parent_id')->get();

        if ($rootNUmber->count() > 2) {
            return response()->json(['errors' => ['rootCategory' => trans('error.user.rootCategory')]], 400);
        }
        //子类个数
        foreach ($rootNUmber as $root) {
            $childrenCount = Category::where('parent_id', $root->parent_id)->whereIn('id', $categoryIds)->count();
            if ($childrenCount > 5) {
                return response()->json(['errors' => ['rootCategory' => trans('error.user.childrenCategory')]], 400);
                break;
            }
        }
        $user = \Auth::user();

        if ($avatar = $request->file('avatar')) {
            $destinationPath = 'assets/avatars/'.date('Y/m');
            $extension = $avatar->getClientOriginalExtension();
            $fileName = hash('ripemd160', time().rand(1000000, 99999999)).'.'.$extension;

            list($width, $height) = getimagesize($avatar);

            $avatarPath = (string) $avatar->move($destinationPath, $fileName);

            // TODO 临时方案

            $avatar = \Image::make($avatarPath);

            $avatar->crop(350, 350);
            $avatar->resize(350, 350);
            $avatar->save($avatarPath);

            $user->avatar = $avatarPath;
            if ($url = Attachment::syncFile($avatarPath)) {
                $user->avatar = $url;
            }
        } elseif ($avatar = $request->get('avatar')) {
            //TODO 写的有问题
            $avatar = \Image::make($avatar);

            $fileName = $user->id.'-avatar-'.uniqid().'.png';

            $avatarPath = 'assets/avatars/'.date('y/m/').$fileName;
            \File::makeDirectory('assets/avatars/'.date('y/m/'), $mode = 0755, true, true);

            $avatar->save($avatarPath);

            $user->avatar = $avatarPath;
            if ($url = Attachment::syncFile($avatarPath)) {
                $user->avatar = $url;
            }
        } else {
            $user->avatar = $request->get('avatar_url');
        }

        $user->fill($request->only(['first_name', 'last_name', 'gender']));
        $user->save();

        $profile = $user->profile;
        $profile->fill($request->input());

        $extra = $profile->extra;

        $want_do = $request->get('want_do');
        $want_do = array_filter($want_do, function ($item) {
            return in_array($item, ['buy', 'sale', 'production']);
        });

        $extra['want_do'] = $want_do;
        $extra['shop_urls'] = $request->get('shop_urls') ?: null;

        $profile->extra = $extra;

        $profile->save();

        $user->categories()->detach();

        foreach ((array) $categoryIds as $categoryId) {
            $category = Category::find($categoryId);

            if ($category) {
                $user->categories()->save($category);
            }
        }

        return $this->response->item($user, new DesignerTransformer());
    }

    /**
     * @apiGroup User
     * @apiDescription 用户金额
     *
     * @api {get} /user/account 用户金额
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 ok
     *{
     *  "amount": "222.00",
     *  "privilege_amount": "2.00",
     *  "account_name": "易晓东"
     *}
     */
    public function account()
    {
        $user = \Auth::user();
        $result['data']['amount'] = $user->amount;
        $result['data']['privilege_amount'] = $user->privilege_amount;
        $result['data']['account_name'] = $user->account_name;

        return $this->response->array($result);
    }

    /**
     * @apiGroup User
     * @apiDescription 用户认证详情(设计师和制造商)
     *
     * @api {get} /user/authentication 用户认证详情(设计师和制造商)
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} [include]  可引入的关系
     * @apiParam {String} [include.user] 认证的用户
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 ok
     *   {
     *     "data": {
     *       "id": 7,
     *       "user_id": 354,
     *       "type": "DESIGNER",
     *       "status": "REFUSE",
     *       "description": "设计师的自我介绍!",
     *       "created_at": "2016-04-06 06:42:36",
     *       "updated_at": "2016-04-21 10:38:00",
     *       "first_name": "四",
     *       "last_name": "李",
     *       "avatarPath": "/home/vagrant/Code/defara.com/public/assets/avatars/16/04/354-avatar-5717435ea1ce7.png",
     *       "gender": "male",
     *       "position_id": "1",
     *       "personal_page_url": "www.baidu.com",
     *       "location": {
     *         "country_region_id": "538",
     *         "province_region_id": "926",
     *         "city_region_id": "929"
     *       },
     *       "brands": [
     *         "alpha",
     *         "智能仓库",
     *         "surface pro 4"
     *       ],
     *       "category_ids": [
     *         "1",
     *         "2"
     *       ],
     *       "educations": [
     *         {
     *           "school": "麻省",
     *           "major": "php"
     *         },
     *         {
     *           "school": "哈弗",
     *           "major": "计算机"
     *         },
     *         {
     *           "school": "纽约",
     *           "major": "科学"
     *         }
     *       ],
     *       "careers": [
     *         {
     *           "company": "Google",
     *           "position": "php"
     *         },
     *         {
     *           "company": "Amazon",
     *           "position": "php"
     *         },
     *         {
     *           "company": "MicroSoft",
     *           "position": "php"
     *         }
     *       ],
     *       "works": [
     *         {
     *           "title": "作品标题",
     *           "cover_picture_id": "1",
     *           "description": "这是一件很时尚的作品!!!作品描述",
     *           "detail_attachments": [
     *             {
     *               "id": "1841",
     *               "name": "b3.jpg",
     *               "tempid": "7l6kk8lgj2"
     *             },
     *             {
     *               "id": "1843",
     *               "name": "b2.jpg",
     *               "tempid": "7l6kk8lgj4"
     *             }
     *           ]
     *         },
     *         {
     *           "title": "第二个作品标题",
     *           "cover_picture_id": "4",
     *           "description": "第二个作品描述",
     *           "detail_attachments": [
     *             {
     *               "name": "b4.jpg",
     *               "tempid": "7l6kk8lgj3",
     *               "id": "1840"
     *             },
     *             {
     *               "name": "b5.jpg",
     *               "tempid": "7l6kk8lgj4",
     *               "id": "1842"
     *             }
     *           ]
     *         }
     *       ],
     *       "user": {
     *         "data": {
     *           "id": 354,
     *           "type": "DESIGNER",
     *           "cellphone": "15828282828",
     *           "email": "test@qq.com",
     *           "avatar": "http://dev.defara.com/home/vagrant/Code/defara.com/public/assets/avatars/16/04/354-avatar-5717435ea1ce7.png",
     *           "first_name": "四",
     *           "last_name": "李",
     *           "gender": "MALE",
     *           "is_email_verified": 0,
     *           "is_cellphone_verified": 0,
     *           "created_at": "2016-03-14 03:30:25",
     *           "updated_at": "2016-04-25 03:33:12",
     *           "status": "ACTIVE",
     *           "birthday": "2016-03-24 15:51:41",
     *           "position": "",
     *           "is_verify": 1,
     *           "logged_at": "2016-04-25 03:33:12",
     *           "account_name": null,
     *           "fullname": "四 李"
     *         }
     *       }
     *     }
     *   }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 404 Not Found
     */
    public function authentication(Request $request)
    {
        $user = \Auth::user();
        $authentication = $user->authentication;

        $meta = [];
        $styles = Style::all();
        $positions = Position::all();
        $serviceResults = ServiceResult::all();

        if (!$authentication) {
            $meta['meta']['styles'] = $styles;
            $meta['meta']['positions'] = $positions;
            $meta['meta']['serviceResults'] = $serviceResults;

            return json_encode($meta);
        }

        return $this->response->item($authentication, new UserAuthenticationTransformer())
            ->addMeta('styles', $styles)
            ->addMeta('positions', $positions)
            ->addMeta('serviceResults', $serviceResults);
    }
    /**
     * @apiGroup User
     * @apiDescription 用户临时信息
     *
     * @api {post} /users/contact/info 用户临时信息
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} contact_info 联系
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201 OK
     */
    public function tempInfo(TempInfoRequest $request)
    {
        $data = [
            'contactInfo' => $request->get('contact_info'),
            'time' => (string) Carbon::now(),
        ];

        $filename = 'tempInfo.json';
        $filesystem = \Storage::disk('local');
        $allData = [];

        if ($filesystem->has($filename)) {
            $allData = $filesystem->get($filename);
            $allData = json_decode($allData, true);
        }

        array_push($allData, $data);

        $filesystem->put($filename, json_encode($allData), $lock = true);

        return $this->response->created();
    }
}
