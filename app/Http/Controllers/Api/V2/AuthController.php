<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Transformers\UserTransformer;
use App\Http\Requests\Api\User\LoginRequest;
use App\Http\Requests\Api\User\SignupRequest;
use App\Http\Requests\Api\User\VerifyCodeRequest;
use App\Http\Requests\Api\User\ResetPasswordRequest;
use App\Http\Requests\Api\User\AccountUpdateRequest;
use App\Http\Requests\Api\User\ForgetPasswordRequest;
use App\Http\Requests\Api\User\VerifyPasswordRequest;
use App\Http\Requests\Api\User\ValidateVerifyCodeRequest;
use Carbon\Carbon;
use App\Repositories\Contracts\UserRepositoryContract;

class AuthController extends BaseController
{
    /**
     * @apiGroup Auth
     * @apiDescription 用户登录
     *
     * @api {post} /auth/login 用户登录
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String} username 用户邮件地址或者电话号码
     * @apiParam {String{6..20}} password 密码
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": {
     *     "id": 1,
     *     "user_group_id": 2,
     *     "cellphone": "15208267701",
     *     "email": null,
     *     "avatar": "http://local.defara/assets/avatars/2015/08/18/6e1cccc435a02fb9f609319774116507d0afa556.jpg",
     *     "first_name": "周",
     *     "last_name": "诚力",
     *     "gender": "MALE",
     *     "find_password": null,
     *     "is_email_verified": 0,
     *     "is_cellphone_verified": 0,
     *     "created_at": "2015-08-14 10:07:56",
     *     "updated_at": "2015-08-21 07:10:36",
     *     "deleted_at": null,
     *     "find_password_at": null,
     *     "amount": "0.00",
     *     "remember_token": null,
     *     "status": "ACTIVE",
     *     "nickname": "XX制衣厂",
     *     "birthday": "0000-00-00 00:00:00",
     *     "privilege_amount": 0,
     *     "position": "",
     *     "verify_content": "",
     *     "is_verify": 1
     *   },
     *   "meta": {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL2xvY2FsLmRlZmFyYVwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTQ1NzQxNzM3OCwiZXhwIjoxNDU3NDIwOTc4LCJuYmYiOjE0NTc0MTczNzgsImp0aSI6ImI2ZGY2ZDYxYWZlYWUxNzFkZGU3MGUwOWI5ZTJkMWZjIn0.dc8rtfm7SqUfd4AjovS43owEZcKv1bQzvA3_bwN-gL8"
     *   }
     * }
     */
    public function login(LoginRequest $request, UserRepositoryContract $userRepository)
    {
        //登录名
        $username = $request->get('username');

        //验证登录名是邮箱还是手机
        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['cellphone'] = $username;

        // 密码
        $credentials['password'] = $request->get('password');

        if (!$token = \JWTAuth::attempt($credentials)) {
            return response()->json(['errors' => [trans('error.auth.invalid_login')]], 401);
        }

        $user = \Auth::user();
        $userRepository->update($user, ['logged_at' => Carbon::now()]);

        return $this->response
            ->item($user, new UserTransformer())
            ->addMeta('token', $token)
            ->addMeta('im_token', $user->im_token);
    }

    /**
     * @apiGroup Auth
     * @apiDescription 用户注册
     *
     * @api {post} /auth/signup 用户注册
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String} username 用户邮件地址或者电话号码
     * @apiParam {String{6..20}} password 密码
     * @apiParam {String{6..20}} password_confirmation 确认密码
     * @apiParam {String} verify_code 验证码
     * @apiParam {String='designer','maker','dealer'} user_type 用户类型
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "data": {
     *     "email": "yu.li@defara.com",
     *     "is_email_verified": true,
     *     "updated_at": "2016-03-09 02:43:48",
     *     "created_at": "2016-03-09 02:43:48",
     *     "id": 220
     *   },
     *   "meta": {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjIyMCwiaXNzIjoiaHR0cDpcL1wvbG9jYWwuZGVmYXJhXC9hcGlcL2F1dGhcL3JlZ2lzdGVyIiwiaWF0IjoxNDU3NDkxNDI4LCJleHAiOjE0NTc0OTUwMjgsIm5iZiI6MTQ1NzQ5MTQyOCwianRpIjoiOWQxZWU0ZWU3Njc3ZTBjYzI3MGJiOWVmZjRiNWQ4ZGYifQ.xR2i_4EDzy9S7Ds3NptcaG0lGYOrxe4McnlDVOMCEpc"
     *   }
     * }
     * @apiErrorExample {json} 表单验证:
     * HTTP/1.1 400 Bad Request
     * {
     *   "message": [
     *     "用户名 不能为空。",
     *     "密码 不能为空。",
     *     "确认密码 不能为空。"
     *   ]
     * }
     */
    public function signup(SignupRequest $request, UserRepositoryContract $userRepository)
    {
        //登录名
        $username = $request->get('username');

        if ($verifyCode = $request->get('verify_code')) {
            if (!$this->checkVerifyCode($username, $verifyCode)) {
                return response()->json(['errors' => ['verify_code' => trans('error.auth.verify_code_wrong')]], 401);
            }
        }

        \Cache::store('database')->forget($this->getVerifyKey($username));

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $data['email'] = $username;
            $data['is_email_verified'] = true;
        } else {
            $data['cellphone'] = $username;
            $data['is_cellphone_verified'] = true;
        }

        $data['password'] = bcrypt($request->get('password'));
        $data['type']= strtoupper($request->get('user_type'));
        $data['status']= ($data['type'] == 'DEALER') ? 'ACTIVE' : 'INACTIVE';

        $createdEntity = $userRepository->create($data);
        list($status, $user) = $createdEntity;

        // 生成token
        $token = \JWTAuth::fromUser($user);

        // 重新获取一下用户信息，为了前端某个数据，以后调整
        $user = $userRepository->find($user->id);

        return $this->response
            ->item($user, new UserTransformer())
            ->addMeta('token', $token)
            ->addMeta('im_token', $user->im_token);
    }

    /**
     * @apiGroup Auth
     * @apiDescription 发送注册验证码
     *
     * @api {post} /auth/verifyCode 发送注册验证码
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String} username 电话或邮箱
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function verifyCode(VerifyCodeRequest $request)
    {
        $username = $request->get('username');
        // 发送验证码
        if (!$this->sendVerifyCode($username)) {
            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }

    /**
     * @apiGroup Auth
     * @apiDescription 忘记密码, 发送与验证码
     *
     * @api {post} /auth/password/forget 忘记密码, 发送与验证码
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {String} username   手机号或邮箱
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     */
    public function forgetPassword(ForgetPasswordRequest $request, UserRepositoryContract $userRepository)
    {
        $username = $request->get('username');

        if ($username) {
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $user = $userRepository->findBy('email', $username);
            } else {
                $user = $userRepository->findBy('cellphone', $username);
            }

            // 用户不存在，或者用户是第三方用户
            if (!$user || !$user->password) {
                return response()->json(['errors' => ['username' => trans('error.auth.user_not_found')]], 404);
            }
        }

        if (!$this->sendVerifyCode($username)) {
            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }
    /**
     * @apiGroup Auth
     * @apiDescription 验证验证码
     *
     * @api {post} /auth/authentication/verifyCode 验证验证码
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} verify_code 验证码
     * @apiParam {String} username 账号
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     *{
     *  "verify_token": "c4ca4238a0b923820dcc509a6f75849b"
     *}
     */
    public function validateVerifyCode(ValidateVerifyCodeRequest $request, UserRepositoryContract $userRepository)
    {
        $username = $request->get('username');
        $verifyCode = $request->get('verify_code');
        // 理论上用户不应该不存在
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $userRepository->findBy('email', $username);
        } else {
            $user = $userRepository->findBy('cellphone', $username);
        }
        // 如果用户不存在，一般不会出现该问题
        if (!$user) {
            return response()->json(['errors' => ['username' => trans('error.auth.user_not_found')]], 404);
        }
        // 验证码错误，添加消息
        if (!$this->checkVerifyCode($username, $verifyCode)) {
            return response()->json(['errors' => ['verify_code' => trans('error.auth.verify_code_wrong')]], 404);
        }

        $verifyToken = md5($user->id.''.uniqid());

        \Cache::store('database')->put($verifyToken, $user->id, 12);

        return $this->response->array(['verify_token' => $verifyToken]);
    }

    /**
     * @apiGroup Auth
     * @apiDescription 重置密码
     *
     * @api {post} /auth/password/reset 重置密码
     * @apiVersion 0.2.0
     * @apiPermission none
     * @apiParam {string} verify_token 验证token
     * @apiParam {String{6..20}} password 密码
     * @apiParam {String{6..20}} password_confirmation 确认密码
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     */
    public function resetPassword(ResetPasswordRequest $request, UserRepositoryContract $userRepository)
    {
        $token = $request->get('verify_token');

        $id = \Cache::store('database')->get($token);

        if (!$id || !$user = $userRepository->find($id)) {
            return $this->response->errorInternal();
        }

        $userRepository->update($user, ['password' => bcrypt($request->get('password'))]);

        \Cache::store('database')->forget($token);

        return $this->response->noContent();
    }

    /**
     * @apiGroup Auth
     * @apiDescription 验证密码
     *
     * @api {post} /auth/password/verification 验证密码
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String{6..20}} password 密码
     * @apiSuccessExample {json} Success-Response:
     *{
     *"verify_token": "ec818776aba9de9a1e3c592b1a610965"
     *}
     */
    public function verifyPassword(VerifyPasswordRequest $request)
    {
        $user = \Auth::user();
        //验证密码
        if (!\Auth::validate(['id' => $user->id, 'password' => $request->get('password')])) {
            return response()->json(['errors' => ['password' => trans('error.auth.invalid_password')]], 404);
        }

        $verifyToken = md5(rand());

        \Cache::store('database')->put($this->getVerifyPasswordKey($user), $verifyToken, 12);

        return $this->response->array(['verify_token' => $verifyToken]);
    }

    /**
     * @apiGroup Auth
     * @apiDescription 修改账号
     *
     * @api {put} /auth/acountName 修改账号
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} username   手机号或邮箱
     * @apiParam {String='cellphone','email'} account_type  账号类型
     * @apiParam {String} verify_token   验证的用户的参数
     * @apiParam {String} verify_code 验证码
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 No Content
     */
    public function accountUpdate(AccountUpdateRequest $request, UserRepositoryContract $userRepository)
    {
        //登录名
        $username = $request->get('username');
        //验证token
        $user = \Auth::user();
        $cacheVerfyToken = \Cache::store('database')->get($this->getVerifyPasswordKey($user));

        if ($cacheVerfyToken != $request->get('verify_token')) {
            return $this->response->errorInternal();
        }

        //账号是否存在
        $accountType = $request->get('account_type');
        $userExists = $userRepository->findBy($accountType, $username);

        if ($userExists) {
            return response()->json(['errors' => ['username' => trans('error.auth.exist_username')]], 403);
        }

        // 验证码错误
        if (!$this->checkVerifyCode($username, $request->get('verify_code'))) {
            return response()->json(['errors' => ['verify_code' => trans('error.auth.verify_code_wrong')]], 400);
        }
        //更新账号
        $userRepository->update($user, [$accountType => $username]);
        // 清除缓存
        \Cache::store('database')->forget($this->getVerifyKey($username));
        \Cache::store('database')->forget($this->getVerifyPasswordKey($user));

        return $this->response->noContent();
    }

    // 根据用户名id生成验证key
    private function getVerifyPasswordKey($user)
    {
        return 'verityPassword-'.$user->id;
    }

    // 根据用户名生成验证key
    private function getVerifyKey($username)
    {
        return 'verifyCode-'.$username;
    }

    //  验证用户名对应的验证码是否正确
    private function checkVerifyCode($username, $verifyCode)
    {
        $cacheKey = $this->getVerifyKey($username);

        // 取得缓存里面的验证码
        $verifyCodeCache = \Cache::store('database')->get($cacheKey);

        //验证码存在或不相等
        if ($verifyCode != $verifyCodeCache) {
            return false;
        }

        return true;
    }

    // 发送验证码，存入cache
    private function sendVerifyCode($username)
    {
        // 发送验证码
        $code = $this->generateVerifyCode(6);

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $ret = $this->sendVerifyEmail($username, $code);
        } else {
            $ret = $this->sendVerifySms($username, $code);
        }

        if ($ret) {
            $expireMinutes = 10;
            $cacheKey = $this->getVerifyKey($username);
            \Cache::store('database')->put($cacheKey, $code, $expireMinutes);
        }

        return $ret;
    }

    // 发送验证邮件
    private function sendVerifyEmail($email, $code)
    {
        $lang = \App::getLocale();
        $view = 'email.verify-code-'.$lang;

        \Mail::send($view, ['code' => $code], function ($message) use ($email) {
            $message->to($email)->subject(trans('auth.defara_verify_code'));
        });

        $ret = count(\Mail::failures()) == 0 ? true : false;

        return $ret;
    }

    // 发送验证短信
    private function sendVerifySms($cellphone, $code)
    {
        $codeMessage = trans('auth.verify_code_sms', ['code' => $code]);

        $sms = \App()->app['sms'];

        return $sms->to($cellphone)->message($codeMessage)->attempt();
    }

    //  生成验证码
    private function generateVerifyCode($bit)
    {
        // 如果是测试环境，验证码为1234
        if (env('APP_DEBUG')) {
            return '1234';
        }

        $startNumber = 0;
        $endNUmber = pow(10, $bit) - 1;

        $verifyCode = sprintf('%06d', mt_rand($startNumber, $endNUmber));

        return $verifyCode;
    }
}
