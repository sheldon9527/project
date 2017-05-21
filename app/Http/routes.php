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

$api->version('v1', ['namespace' => 'App\Http\Controllers\Api\V1'], function ($api) {

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
    /**
     * 目的地地址
     */
    $api->get('teach/addresses', 'TeachAddressController@index');
    $api->get('teach/addresses/{id}', 'TeachAddressController@show');
    $api->post('teach/addresses', 'TeachAddressController@store');
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
