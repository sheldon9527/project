<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
 */
Route::pattern('id', '[0-9]+');
Route::pattern('oid', '[0-9]+');
Route::pattern('alpha', '[A-Za-z]+');

$api = app('api.router');

$api->version('v2', ['namespace' => 'App\Http\Controllers\Api\V2'], function ($api) {

    // 测试用的路由，查看服务器相关配置是否正确
    if (env('APP_DEBUG')) {
        $api->get('db/{param}', function ($param) {
            \Artisan::call('db:seed', [
                '--class' => $param,
            ]);
        });
        $api->get('config/{path}', function ($path) {
            return config($path) ?: 'config error';
        });

        $api->get('env/{key}', function ($key) {

            $result = env($key) ?: 'env error';

            return response()->json($result);
        });

        $api->get('artisan/{name}', function ($name) {
            \Artisan::call($name);

            return response()->json(\Artisan::output());
        });

        $api->get('migrate', function () {
            \Artisan::call('migrate');

            return response()->json(\Artisan::output());
        });
    }

    $api->get('gitlog', function () {
        $info = shell_exec('git log -1');
        $info = str_replace("\n", '<br>', $info);

        return response($info);
    });

    //app 用户临时信息
    $api->post('users/contact/info', 'UserController@tempInfo');

    // 搜索自动补全对应的名称
    $api->get('search/autocomplete', 'SearchController@autocomplete');
    $api->get('search', 'SearchController@search');

    # auth
    //登录
    $api->post('auth/login', 'AuthController@login');
    //注册
    $api->post('auth/signup', 'AuthController@signup');
    //发送验证码
    $api->post('auth/verifyCode', 'AuthController@verifyCode');
    //验证验证码
    $api->post('auth/authentication/verifyCode', 'AuthController@validateVerifyCode');
    //忘记密码，发送验证码
    $api->post('auth/password/forget', 'AuthController@forgetPassword');
    //忘记密码，重置密码
    $api->post('auth/password/reset', 'AuthController@resetPassword');

    // 第三方登录
    $api->get('oauth/{provider}', 'OAuthController@redirectToProvider');
    $api->get('oauth/{provider}/callback', 'OAuthController@handleProviderCallback');

    $api->post('oauth/bindAccount', 'OAuthController@bindAccount');
    $api->post('oauth/createAccount', 'OAuthController@createAccount');

    //soaicls列表
    $api->get('socials', 'SocialController@index');

    # recommendations 推荐
    //推荐列表
    $api->get('recommendations/home', 'RecommendController@home');
    $api->get('recommendations', 'RecommendController@index');

    $api->get('home/designers/filter', 'HomeController@showFilterData');

    //设计师
    //设计师列表
    $api->get('designers', 'DesignerController@index');
    //设计师详情
    $api->get('designers/{id}', 'DesignerController@show');
    //某个人 work
    $api->get('designers/{id}/works', 'WorkController@designerIndex');
    //作品详细
    $api->get('works/{id}', 'WorkController@show');
    //某个人的服务
    $api->get('designers/{id}/services', 'ServiceController@designerIndex');
    //服务详细
    $api->get('services/{id}', 'ServiceController@show');

    //某个人的服务类别work
    $api->get('designers/{id}/service/works', 'InquiryServiceController@designerIndex');

    # factories 工厂
    //工厂详情
    $api->get('makers/{id}', 'MakerController@show');

    $api->get('makers', 'MakerController@index');

    // 分类列表
    $api->get('categories', 'CategoryController@index');
    //国家列表
    $api->get('regions', 'RegionController@index');
    $api->get('countries', 'RegionController@countryIndex');
    $api->get('hot/countries', 'RegionController@hotCountryIndex');
    //国家列表json格式
    $api->get('json/countries', 'RegionController@JsonCountryIndex');
    $api->get('formatRegion', 'RegionController@formatRegion');

    // 银行列表
    $api->get('banks', 'BankController@index');

    // 服务列表
    $api->get('services', 'ServiceController@index');

    // alipay支付
    $api->get('alipay/return', 'PaymentController@alipayReturn');
    $api->post('alipay/notify', 'PaymentController@alipayNotify');
    // paypal 支付
    $api->get('paypal/return', [
        'as' => 'paypal.return',
        'uses' => 'PaymentController@paypalReturn',
    ]);

    $api->get('paypal/cancel', [
        'as' => 'paypal.cancel',
        'uses' => 'PaymentController@paypalCancel',
    ]);

    // 登录后的用户可访问
    $api->group(['middleware' => 'api.auth'], function ($api) {
        // 预览合同
        $api->post('contracts/preview', 'ContractController@preview');
        $api->get('order/contract', 'ContractController@orderShow');

        $api->post('attachments/download', 'AttachmentController@download');
        // $api->post('attachments/download', 'AttachmentController@download');
        # attachment 图片上传
        $api->post('attachments', 'AttachmentController@store');
        $api->put('attachments/{id}', 'AttachmentController@update');
        $api->delete('attachments/{id}', 'AttachmentController@destroy');

        //修改密码
        $api->post('user/password', 'UserController@updatePassword');

        // 修改密码
        $api->put('auth/password', 'AuthController@passwordUpdate');
        // 验证密码
        $api->post('auth/password/verification', 'AuthController@verifyPassword');
        // 修改账号（手机或邮箱）
        $api->put('auth/acountName', 'AuthController@accountUpdate');

        # 设计师的服务列表 services
        // 当前登陆用户的服务列表
        $api->get('user/services', 'ServiceController@userIndex');
        // 服务添加
        $api->post('services', 'ServiceController@store');
        // 服务修改
        $api->put('services/{id}', 'ServiceController@update');
        // 服务删除
        $api->delete('services/{id}', 'ServiceController@destroy');

        //消息
        $api->get('user/notifications', 'NotificationController@index');
        $api->get('notifications/{id}', 'NotificationController@show');

        $api->put('notifications/{id}', 'NotificationController@update');
        $api->post('notifications/read', 'NotificationController@read');
        $api->delete('notifications/{id}', 'NotificationController@destroy');
        $api->delete('notifications', 'NotificationController@multiDestory');

        //order Messages
        $api->get('order/messages', 'OrderMessageController@index');
        $api->delete('order/messages/{id}', 'OrderMessageController@destroy');

        # favorite 收藏
        // 收藏列表
        $api->get('user/favorites', 'FavoriteController@index');
        // 添加收藏
        $api->post('favorites', 'FavoriteController@store');
        // 取消收藏
        $api->delete('favorites/{id}', 'FavoriteController@destroy');
        $api->delete('favorites', 'FavoriteController@favoriteDestroy');

        // 修改用户信息，头像，性别，姓名
        $api->post('user', 'UserController@update');
        //用户profile
        $api->post('user/profile', 'UserController@updateProfile');

        //用户的认证详情
        $api->get('user/authentication', 'UserController@authentication');

        //设计师works
        $api->get('user/works', 'WorkController@userIndex');
        $api->post('works', 'WorkController@store');
        $api->put('works/{id}', 'WorkController@update');
        $api->delete('works/{id}', 'WorkController@destroy');

        //user账号信息
        $api->get('user', 'UserController@userShow');
        $api->post('user/password', 'UserController@updatePassword');

        #设计师和制造商认证
        $api->post('designer/authentications', 'DesignerController@authentication');
        $api->post('maker/authentications', 'MakerController@authentication');

        //委托设计
        $api->post('service/orders', 'ServiceOrderController@store');
        $api->get('service/orders', 'ServiceOrderController@index');
        $api->get('service/orders/{id}', 'ServiceOrderController@show');
        $api->put('service/orders/{id}', 'ServiceOrderController@update');

        //询价设计服务
        $api->get('inquiry_service/orders', 'InquiryServiceOrderController@index');
        $api->get('inquiry_service/orders/{id}', 'InquiryServiceOrderController@show');
        $api->put('inquiry_service/orders/{id}', 'InquiryServiceOrderController@update');

        //询价设计服务交流
        $api->post('order/comments', 'CommentController@store');
        // 显示订单详情
        $api->get('order/{orderNo}', 'OrderController@show');
        // // 付款
        $api->post('order/pay', 'PaymentController@pay');

        #询价服务
        // 添加设计服务
        $api->get('inquiry_service', 'InquiryServiceController@index');
        $api->post('inquiry_service', 'InquiryServiceController@store');
        $api->put('inquiry_service/{id}', 'InquiryServiceController@update');
        $api->get('inquiry_service/{id}', 'InquiryServiceController@show');
        $api->delete('inquiry_service/{id}', 'InquiryServiceController@destroy');

        //询价单
        // 当前用户订单列表
        $api->get('inquiry/orders', 'InquiryOrderController@index');
        $api->get('inquiry/orders/{id}', 'InquiryOrderController@show');
        // 更新，回复及取消
        $api->put('inquiry/orders/{id}', 'InquiryOrderController@update');
        // 创建询价单
        // $api->post('inquiry/orders', 'InquiryOrderController@store');
        // 发布的制造商列表
        $api->get('inquiry/orders/{id}/publish/makers', 'InquiryOrderController@makersIndex');

        //打样单
        // $api->post('sample/orders', 'SampleOrderController@store');
        $api->get('sample/orders', 'SampleOrderController@index');
        $api->get('sample/orders/{id}', 'SampleOrderController@show');
        //打样单提交记录
        $api->post('sample/orders/{id}/records', 'SampleOrderRecordController@store');

        $api->put('sample/orders/{orderId}/records/{id}', 'SampleOrderRecordController@update');

        // po单
        $api->post('purchase/orders', 'PurchaseOrderController@store');
        $api->get('purchase/orders', 'PurchaseOrderController@index');
        $api->get('purchase/orders/{id}', 'PurchaseOrderController@show');
        $api->put('purchase/orders/{id}', 'PurchaseOrderController@update');

        // 生产订单
        // $api->post('production/orders', 'ProductionOrderController@store');
        $api->put('production/orders/{id}', 'ProductionOrderController@update');
        $api->get('production/orders', 'ProductionOrderController@index');
        $api->get('production/orders/{id}', 'ProductionOrderController@show');

        //申诉单
        $api->post('appeal/orders', 'AppealOrderController@store');
        $api->get('appeal/orders', 'AppealOrderController@index');
        $api->put('appeal/orders/{id}', 'AppealOrderController@update');
        $api->get('appeal/orders/{id}', 'AppealOrderController@show');
        $api->delete('appeal/orders/{id}', 'AppealOrderController@delete');

        //提现单
        $api->post('withdraw/orders', 'WithdrawOrderController@store');
        $api->get('withdraw/orders', 'WithdrawOrderController@index');

        //充值单
        $api->post('recharge/orders', 'RechargeOrderController@store');
        $api->get('recharge/orders', 'RechargeOrderController@index');
        //获取资金
        $api->get('user/account', 'UserController@account');
        // 资金流水
        $api->get('user/transactions', 'TransactionController@userIndex');
        // 地址管理
        $api->get('user/addresses', 'AddressController@index');
        $api->post('addresses', 'AddressController@store');
        $api->get('addresses/{id}', 'AddressController@show');
        $api->put('addresses/{id}', 'AddressController@update');
        $api->delete('addresses/{id}', 'AddressController@destroy');

        $api->get('admins', 'AdminController@index');
    });
});

/*
 * 运营后台
 */
Route::group(['namespace' => 'Admin', 'prefix' => 'manager'], function () {
    // 登录页面
    Route::get('auth/login', [
        'as' => 'admin.auth.login.get',
        'uses' => 'AuthController@getLogin',
    ]);
    // 登录提交
    Route::post('auth/login', [
        'as' => 'admin.auth.login.post',
        'uses' => 'AuthController@postLogin',
    ]);
    // 替代用户登陆
    Route::get('replacement/login/{id}', [
        'as' => 'admin.auth.replacement.login',
        'uses' => 'AuthController@replaceLogin',
    ]);
    Route::group(['middleware' => ['admin.auth']], function () {
        #登出
        Route::get('logout', [
            'as' => 'admin.auth.logout',
            'uses' => 'AuthController@logout',
        ]);
        Route::get('/', function () {
            return redirect(route('admin.dashboard'));
        });
        # Dashboard
        // 后台统计信息
        Route::get('dashboard', [
            'as' => 'admin.dashboard',
            'uses' => 'DashboardController@dashboard',
        ]);
        /**
         * 目的地管理
         */
         Route::get('teach/addresses', [
             'as' => 'admin.teach.addresses.index',
             'uses' => 'TeachAddressController@index',
         ]);
         Route::get('teach/addresses/create', [
             'as' => 'admin.teach.addresses.create',
             'uses' => 'TeachAddressController@create',
         ]);
         Route::post('teach/addresses', [
             'as' => 'admin.teach.addresses.store',
             'uses' => 'TeachAddressController@store',
         ]);
         Route::get('teach/addresses/{id}', [
             'as' => 'admin.teach.addresses.show',
             'uses' => 'TeachAddressController@show',
         ]);
         Route::delete('teach/addresses/{id}', [
             'as' => 'admin.teach.addresses.destory',
             'uses' => 'TeachAddressController@destory',
         ]);
         Route::get('teach/addresses/{id}/edit', [
             'as' => 'admin.teach.addresses.edit',
             'uses' => 'TeachAddressController@edit',
         ]);
         Route::put('teach/addresses/{id}', [
             'as' => 'admin.teach.addresses.update',
             'uses' => 'TeachAddressController@update',
         ]);
         Route::get('teach/addresses/multiDestory', [
             'as' => 'admin.teach.addresses.multiDestory',
             'uses' => 'TeachAddressController@multiDestory',
         ]);
         Route::get('teach/addresses/multiUpdate', [
             'as' => 'admin.teach.addresses.multiUpdate',
             'uses' => 'TeachAddressController@multiUpdate',
         ]);
         /**
          * 目的地的回收
          */
          Route::get('teach/addresses/recycle', [
              'as' => 'admin.teach.addresses.recycle.index',
              'uses' => 'TeachAddressController@recycleIndex',
          ]);
          /**
           * 目的地审批
           */
          Route::get('teach/addresses/approval', [
              'as' => 'admin.teach.addresses.approval.index',
              'uses' => 'TeachAddressController@approvalIndex',
          ]);

        /**
         * admins
         */
        Route::get('admins', [
            'as' => 'admin.admins.index',
            'uses' => 'AdminController@index',
        ]);
        Route::post('admins', [
            'as' => 'admin.admins.store',
            'uses' => 'AdminController@store',
        ]);
        Route::get('admins/{id}/edit', [
            'as' => 'admin.admins.edit',
            'uses' => 'AdminController@edit',
        ]);
        Route::put('admins/{id}', [
            'as' => 'admin.admins.update',
            'uses' => 'AdminController@update',
        ]);
        Route::get('admins/{id}', [
            'as' => 'admin.admins.show',
            'uses' => 'AdminController@show',
        ]);
        Route::delete('admins/{id}', [
            'as' => 'admin.admins.destroy',
            'uses' => 'AdminController@destroy',
        ]);
        /**
         * Attachment
         */
        Route::post('attachments/download', [
             'as' => 'admin.attachments.download',
             'uses' => 'AttachmentController@download',
        ]);
        Route::get('attachments', [
            'as' => 'admin.attachments.index',
            'uses' => 'AttachmentController@index',
        ]);
        Route::post('attachments', [
            'as' => 'admin.attachments.store',
            'uses' => 'AttachmentController@store',
        ]);
        Route::delete('attachments/{id}', [
            'as' => 'admin.attachments.destroy',
            'uses' => 'AttachmentController@destroy',
        ]);
        /**
         * categories
         */
        Route::get('categories', [
            'as' => 'admin.categories.index',
            'uses' => 'CategoryController@index',
        ]);
        Route::post('categories', [
            'as' => 'admin.categories.store',
            'uses' => 'CategoryController@store',
        ]);
        Route::put('categories/{id}', [
            'as' => 'admin.categories.update',
            'uses' => 'CategoryController@update',
        ]);
        Route::delete('categories/{id}', [
            'as' => 'admin.categories.destroy',
            'uses' => 'CategoryController@destroy',
        ]);
    });
});
