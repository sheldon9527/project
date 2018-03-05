<?php

use Illuminate\Http\Request;

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api\V1',
    'middleware' => 'serializer:array',
], function ($api) {
    /**
     * 目的地地址
     */
    $api->get('teach/addresses', 'TeachAddressController@index');
    $api->get('teach/addresses/{id}', 'TeachAddressController@show');
    $api->post('teach/addresses', 'TeachAddressController@store');
});
