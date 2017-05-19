<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Models\Social;
use App\Models\User;
use App\Models\UserSocial;

class OAuthController extends BaseController
{
    /**
     * @apiGroup Auth
     * @apiDescription 第三方登录或绑定
     *
     * @api {get} /oauth/{provider} 第三方登录或绑定
     * @apiPermission none
     * @apiVersion 0.2.0
     * @apiParam {String} provider  登录类型
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 302
     *     跳转到第三方平台进行登录
     *     跳转回来后
     *         如果用户已存在 会跳转到front_callback_url?token=xxx
     *         如果用户不存在 会跳转到front_callback_url?state=xxx
     */
    public function redirectToProvider($provider, Request $request)
    {
        $socialNames = $this->getSocialNames();

        if (!in_array($provider, $socialNames)) {
            $this->response->errorBadRequest();
        }

        // 此处做特殊处理，因为想让twitter走guzzlehttp库，方便代理
        // 暂时写死了
        if (config('proxy.enable') && $provider == 'twitter') {
            return $this->twitterRedirect($request);
        }

        $redirect = \Socialize::with($provider)->redirect();

        /*
         * TODO 随机的字符串还是有可能重复的
         */
        $state = $request->getSession()->get('state');
        $stateData = [
            'front_callback_url' => $request->get('front_callback_url'),
            'provider' => $provider,
        ];
        // 如果用户登录了，说明是绑定，那么存入用户id，跳转回来后绑定
        if ($user = $this->user()) {
            $stateData['user_id'] = $user->id;
        }
        // 缓存20分钟足够了吧
        \Cache::store('database')->put($state, $stateData, 60);

        return $redirect;
    }

    public function twitterRedirect($request)
    {
        $config = config('services.twitter');
        $connection = new TwitterOAuth($config['client_id'], $config['client_secret']);

        $connection->setProxy([
            'CURLOPT_PROXY' => '127.0.0.1',
            'CURLOPT_PROXYUSERPWD' => '',
            'CURLOPT_PROXYPORT' => 7777,
        ]);
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $config['redirect']));

        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        $state = $request_token['oauth_token'];
        $stateData = [
            'front_callback_url' => $request->get('front_callback_url'),
            'provider' => 'twitter',
            'oauth_token' => $request_token['oauth_token'],
            'oauth_token_secret' => $request_token['oauth_token_secret'],
        ];
        // 如果用户登录了，说明是绑定，那么存入用户id，跳转回来后绑定
        if ($user = $this->user()) {
            $stateData['user_id'] = $user->id;
        }
        // 缓存20分钟足够了吧
        \Cache::store('database')->put($state, $stateData, 60);

        return redirect($url);
    }

    // 没时间封装了, 先这样实现
    public function twitterCallback($request)
    {
        $state = $request->get('oauth_token');

        try {
            $stateData = \Cache::store('database')->pull($state);

            $config = config('services.twitter');
            $connection = new TwitterOAuth($config['client_id'], $config['client_secret'], $stateData['oauth_token'], $stateData['oauth_token_secret']);

            $connection->setProxy([
                'CURLOPT_PROXY' => '127.0.0.1',
                'CURLOPT_PROXYUSERPWD' => '',
                'CURLOPT_PROXYPORT' => 7777,
            ]);

            $access_token = $connection->oauth('oauth/access_token', [
                'oauth_verifier' => $request->get('oauth_verifier'),
            ]);

            $connection = new TwitterOAuth($config['client_id'], $config['client_secret'], $access_token['oauth_token'], $access_token['oauth_token_secret']);

            $connection->setProxy([
                'CURLOPT_PROXY' => '127.0.0.1',
                'CURLOPT_PROXYUSERPWD' => '',
                'CURLOPT_PROXYPORT' => 7777,
            ]);

            $user = $connection->get('account/verify_credentials');
            if (!$user) {
                abort(500);
            }

            $socialData = $this->parseTwitterSocialiteData($user);

        } catch (\Exception $e) {
            $frontCallbackUrl = config('domain.login');

            return redirect($frontCallbackUrl);
        }

        // 如果用户已经登录则为用户绑定
        if (array_key_exists('user_id', $stateData)) {
            $user = User::find($stateData['user_id']);
            $this->bindToUser($user, $socialData, 'twitter');
            $frontCallbackUrl = url($stateData['front_callback_url']);
        } else {
            $social = Social::where('name', 'twitter')->first();
            $userSocial = UserSocial::where([
                'remote_id' => $socialData['remote_id'],
                'social_id' => $social->id,
            ])->first();

            if ($userSocial) {
                // 用户已存在，直接登录
                $user = $userSocial->user;
                $token = \JWTAuth::fromUser($user);
                // 将token返回给前端，前端可以拿着token请求用户数据
                $queryString = http_build_query(['token' => $token]);
            } else {
                //选择绑定或新建

                // 将state返回给前端，前端选择新建或者绑定的时候带着state
                // 顺便返回一些信息给前端显示
                $queryString = http_build_query([
                    'state' => $state,
                    'name' => $socialData['nickname'],
                    'avatar' => $socialData['avatar'],
                ]);
                $stateData['socialData'] = $socialData;
                \Cache::store('database')->put($state, $stateData, 60);
            }

            $frontCallbackUrl = url($stateData['front_callback_url'].'?'.$queryString);
        }

        return redirect($frontCallbackUrl);
    }

    /**
     * [handleProviderCallback oauth回调地址].
     *
     * @param [string] $provider [provider名称]
     *
     * @return [type] [description]
     */
    public function handleProviderCallback($provider, Request $request)
    {
        $socialNames = $this->getSocialNames();

        if (!in_array($provider, $socialNames)) {
            $this->response->errorBadRequest();
        }

        // 此处做特殊处理，因为想让twitter走guzzlehttp库，方便代理
        // 暂时写死了
        if (config('proxy.enable') && $provider == 'twitter') {
            return $this->twitterCallback($request);
        }

        try {
            $state = $request->get('state');
            $stateData = \Cache::store('database')->pull($state);

            if (!$stateData) {
                abort(500);
            }

            $socialiteUser = \Socialize::driver($provider)->user();
            $socialData = $this->parseSocialiteData($socialiteUser);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            $frontCallbackUrl = config('domain.login');

            return redirect($frontCallbackUrl);
        }

        // 如果用户已经登录则为用户绑定
        if (array_key_exists('user_id', $stateData)) {
            $user = User::find($stateData['user_id']);
            $this->bindToUser($user, $socialData, $provider);
            $frontCallbackUrl = url($stateData['front_callback_url']);
        } else {
            $social = Social::where('name', $provider)->first();
            $userSocial = UserSocial::where([
                'remote_id' => $socialData['remote_id'],
                'social_id' => $social->id,
            ])->first();

            if ($userSocial) {
                // 用户已存在，直接登录
                $user = $userSocial->user;
                $token = \JWTAuth::fromUser($user);
                // 将token返回给前端，前端可以拿着token请求用户数据
                $queryString = http_build_query(['token' => $token]);
            } else {
                //选择绑定或新建

                // 将state返回给前端，前端选择新建或者绑定的时候带着state
                // 顺便返回一些信息给前端显示
                $queryString = http_build_query([
                    'state' => $state,
                    'name' => $socialData['nickname'],
                    'avatar' => $socialData['avatar'],
                ]);
                $stateData['socialData'] = $socialData;
                \Cache::store('database')->put($state, $stateData, 60);
            }

            $frontCallbackUrl = url($stateData['front_callback_url'].'?'.$queryString);
        }

        return redirect($frontCallbackUrl);
    }

    // 获取所有社交账号的名字
    protected function getSocialNames()
    {
        return Social::lists('name')->toArray();
    }

    /**
     * @apiGroup Auth
     * @apiDescription 第三方登录后绑定现有账号
     *
     * @api {post} /oauth/bindAccount 第三方登录后绑定现有账号
     * @apiPermission none
     * @apiVersion 0.2.0
     * @apiParam {String} state 状态码，跳转过后返回的标示码，用户对应用户数据
     * @apiParam {String} username 用户邮件地址或者电话号码
     * @apiParam {String{6..20}} password 密码
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "data": {
     *          "avatar": "http://tp1.sinaimg.cn/5660763112/180/0/1",
     *          "type": "MAKER",
     *          "status": "INACTIVE",
     *          "updated_at": "2016-03-25 06:09:57",
     *          "created_at": "2016-03-25 06:09:57",
     *          "id": 368
     *        },
     *        "meta": {
     *          "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjM2OCwiaXNzIjoiaHR0cDpcL1wvYWxwaGEuZGVmYXJhLmNvbVwvYXBpXC9vYXV0aFwvY3JlYXRlQWNjb3VudCIsImlhdCI6MTQ1ODg4NjE5NywiZXhwIjoxNDU5MTAyMTk3LCJuYmYiOjE0NTg4ODYxOTcsImp0aSI6ImFlODBjZmM3YWNjZDY3NjllYmQ1ODUzMjIzNjgyNGE4In0.NG2VJmhZ2yfiWvzMxWCYwDGUYWfkR7k2srfEK-fCJN8"
     *        }
     *      }
     */
    public function bindAccount(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'state' => 'required|string',
            'username' => 'required',
            'password' => 'required|alpha_num|between:6,20',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages()->all());
        }

        $state = $request->get('state');
        //登录名
        $username = $request->get('username');
        //密码
        $password = $request->get('password');

        //验证登录名是邮箱还是手机
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            //email
            $credentials = [
                'email' => $username,
                'password' => $password,
            ];
        } else {
            //phone
            $credentials = [
                'cellphone' => $username,
                'password' => $password,
            ];
        }

        if (!$token = \JWTAuth::attempt($credentials)) {
            $validator->after(function ($validator) {
                $validator->errors()->add('message', trans('error.auth.invalid_login'));
            });
        }

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages()->all());
        }

        $user = \Auth::user();
        $stateData = \Cache::store('database')->pull($state);
        $this->bindToUser($user, $stateData['socialData'], $stateData['provider']);

        return $this->response
            ->item($user, new UserTransformer())
            ->addMeta('token', $token);
    }

    /**
     * @apiGroup Auth
     * @apiDescription 第三方登录后创建新账号
     *
     * @api {post} /oauth/createAccount 第三方登录后创建新账号
     * @apiPermission none
     * @apiVersion 0.2.0
     * @apiParam {String} state 状态码，跳转过后返回的标示码，用户对应用户数据
     * @apiParam {String} user_type 用户类型 designer,maker,dealer
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "data": {
     *          "avatar": "http://tp1.sinaimg.cn/5660763112/180/0/1",
     *          "type": "MAKER",
     *          "status": "INACTIVE",
     *          "updated_at": "2016-03-25 06:09:57",
     *          "created_at": "2016-03-25 06:09:57",
     *          "id": 368
     *        },
     *        "meta": {
     *          "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjM2OCwiaXNzIjoiaHR0cDpcL1wvYWxwaGEuZGVmYXJhLmNvbVwvYXBpXC9vYXV0aFwvY3JlYXRlQWNjb3VudCIsImlhdCI6MTQ1ODg4NjE5NywiZXhwIjoxNDU5MTAyMTk3LCJuYmYiOjE0NTg4ODYxOTcsImp0aSI6ImFlODBjZmM3YWNjZDY3NjllYmQ1ODUzMjIzNjgyNGE4In0.NG2VJmhZ2yfiWvzMxWCYwDGUYWfkR7k2srfEK-fCJN8"
     *        }
     *      }
     */
    public function createAccount(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'state' => 'required|string',
            'user_type' => 'required|in:designer,maker,dealer,DESIGNER,MAKER,DEALER',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages()->all());
        }

        $state = $request->get('state');
        $stateData = \Cache::store('database')->pull($state);

        if (!$stateData) {
            abort(500);
        }

        $socialData = $stateData['socialData'];

        $user = new User();
        $user->avatar = $socialData['avatar'];
        $user->cacheAvatar();
        $user->type = strtoupper($request->get('user_type'));
        $user->status = 'INACTIVE';
        $user->save();

        $this->bindToUser($user, $socialData, $stateData['provider']);

        $token = \JWTAuth::fromUser($user);

        return $this->response
            ->item($user, new UserTransformer())
            ->addMeta('token', $token);
    }

    protected function bindToUser($user, $socialData, $provider)
    {
        $social = Social::where('name', $provider)->first();

        // 防止一个第三方绑定多个账号
        $userSocial = UserSocial::where('social_id', $social->id)
            ->where('remote_id', $socialData['remote_id'])
            ->exists();

        if ($userSocial) {
            return;
        }

        $userSocial = new UserSocial();
        $userSocial->user()->associate($user);
        $userSocial->social()->associate($social);
        $userSocial->fill($socialData);
        $userSocial->save();

        return $userSocial;
    }

    protected function parseSocialiteData($socialiteUser)
    {
        return [
            'name' => $socialiteUser->getName(),
            'nickname' => $socialiteUser->getNickname(),
            'email' => $socialiteUser->getEmail(),
            'avatar' => $socialiteUser->getAvatar(),
            'remote_id' => $socialiteUser->getId(),
        ];
    }

    protected function parseTwitterSocialiteData($socialiteUser)
    {
        return [
            'name' => $socialiteUser->name,
            'nickname' => $socialiteUser->screen_name,
            'email' => property_exists($socialiteUser, 'email') ? $socialiteUser->email : null,
            'avatar' => $socialiteUser->profile_image_url_https,
            'remote_id' => $socialiteUser->id,
        ];
    }

    //oauth service pick1
    public function getAuthorize()
    {
        $authParams = \Authorizer::getAuthCodeRequestParams();
        $formParams = array_except($authParams, 'client');
        $formParams['client_id'] = $authParams['client']->getId();

        return view('oauth.authorization-form', ['params' => $formParams, 'client' => $authParams['client']]);
    }

    // 授权后生成code，跳转回到客户端
    public function postAuthorize(Request $request)
    {

        // 拒绝就跳转会defara
        if ($request->get('deny')) {
            $redirectUri = \Authorizer::authCodeRequestDeniedRedirectUri();

            return redirect($redirectUri);
        }

        $validator = \Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|alpha_num|between:6,20',
        ], [
            'username.required' => trans('error.oauth.username'),
            'password.required' => trans('error.oauth.password'),
            'password.alpha_num' => trans('error.oauth.number'),
        ]);

        $username = $request->get('username');
        //验证登录名是邮箱还是手机
        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['cellphone'] = $username;

        $credentials['password'] = $request->get('password');

        if (!$validator->fails() && !\Auth::attempt($credentials)) {
            $validator->after(function ($validator) {
                $validator->errors()->add('cellphone', trans('error.oauth.invalid_login'));
            });
        }

        $authParams = \Authorizer::getAuthCodeRequestParams();

        if ($validator->fails()) {
            $formParams = array_except($authParams, 'client');
            $formParams['client_id'] = $authParams['client']->getId();

            $request->flashOnly('cellphone');

            return view('oauth.authorization-form', [
                    'params' => $formParams,
                    'client' => $authParams['client'],
                ])
                ->withErrors($validator->messages());
        }

        $authParams['user_id'] = \Auth::user()->id;
        $redirectUri = \Authorizer::issueAuthCode('user', $authParams['user_id'], $authParams);

        return redirect($redirectUri);
    }

    // 获得user资源
    public function getUser()
    {
        $userId = \Authorizer::getResourceOwnerId();
        $user = \App\Models\User::find($userId);

        if (!$user) {
            abort(404);
        }

        $userDataArray = $user->toArray();
        $userData['userid'] = $userDataArray['id'];
        $userData['avatar'] = url($userDataArray['avatar']);
        $userData['gender'] = $userDataArray['gender'];
        $userData['birthday'] = $userDataArray['birthday'];
        $userData['nickname'] = $this->getNickname($userDataArray);
        $userData['role'] = $userDataArray['type'];

        return $userData;
    }

    private function getNickname($userDataArray)
    {
        if ($userDataArray['first_name'] && $userDataArray['last_name']) {
            $nickname = $userDataArray['first_name'].$userDataArray['last_name'];
        } elseif ($userDataArray['email']) {
            $nickname = explode('@', $userDataArray['email'])[0];
        } else {
            $nickname = $userDataArray['cellphone'];
        }

        return $nickname;
    }

    //defara from pick1

    //pick1 login
    public function redirect(Request $request)
    {
        // 语言设置，默认为中文
        $language = $request->get('language') ?: 'zh';
        $config = config('services.pick1');
        $state = str_random('16');
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect'],
            'response_type' => 'code',
            'state' => $state,
            'language' => $language,
        ];
        $redirectUrl = $config['url'].'?'.http_build_query($params);

        $stateData = [
            'front_callback_url' => $request->get('front_callback_url'),
        ];

        if ($user = \Auth::user()) {
            $stateData['user_id'] = $user->id;
        }

        \Cache::store('database')->put($state, $stateData, 60);

        return redirect($redirectUrl);
    }

    //pick1 callback
    public function callback(Request $request)
    {
        $config = config('services.pick1');
        $code = $request->get('code');

        $state = $request->get('state');
        $stateData = \Cache::store('database')->pull($state);

        if (!$code) {
            return redirect($stateData['front_callback_url']);
        }

        try {
            $httpClient = new \GuzzleHttp\Client();
            // 获取access_token
            $response = $httpClient->post($config['access_token'], [
                'headers' => ['Accept' => 'application/json'],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'redirect_uri' => $config['redirect'],
                ],
            ]);

            $accessToken = json_decode($response->getBody(), true)['access_token'];
            // 获取user信息
            $userData = $httpClient->get($config['user'].'?access_token='.$accessToken);
            $socialData = json_decode($userData->getBody(), true);

            $socialData['remote_id'] = $socialData['id'];

            \DB::beginTransaction();
            // 如果用户已经登录则为用户绑定
            if ($stateData && array_key_exists('user_id', $stateData)) {
                $user = User::find($stateData['user_id']);
                $this->bindToUser($user, $socialData, 'pick');
                $frontCallbackUrl = url($stateData['front_callback_url']);
            } else {
                $social = Social::where('name', 'pick')->first();
                $userSocial = UserSocial::where([
                    'remote_id' => $socialData['remote_id'],
                    'social_id' => $social->id,
                ])->first();

                if ($userSocial) {
                    $user = $userSocial->user;
                    $token = \JWTAuth::fromUser($user);
                    $queryString = http_build_query(['token' => $token]);
                } else {
                    //选择绑定或新建
                    $queryString = http_build_query([
                        'state' => $state,
                        'name' => $socialData['name'],
                        'avatar' => $socialData['avatar'],
                    ]);

                    $data['socialData'] = $socialData;
                    \Cache::store('database')->put($state, $data, 60);
                }

                $frontCallbackUrl = url($stateData['front_callback_url'].'?'.$queryString);
            }
            \DB::commit();

            return redirect($frontCallbackUrl);
        } catch (\Exception $e) {
            \DB::rollBack();

            abort(500);
        }
    }
}
