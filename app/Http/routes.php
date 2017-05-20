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

$api->version('v2', ['namespace' => 'App\Http\Controllers\Api\V1'], function ($api) {

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
          Route::get('teach/addresses/{id}/status', [
              'as' => 'admin.teach.addresses.status.update',
              'uses' => 'TeachAddressController@statusUpdate',
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
