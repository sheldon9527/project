<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
     */

    'mailgun'   => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'mandrill'  => [
        'secret' => '',
    ],

    'ses'       => [
        'key'    => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe'    => [
        'model'  => 'User',
        'secret' => '',
    ],

    'facebook'  => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI'),
    ],

    'twitter'   => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_REDIRECT_URI'),
    ],

    'google'    => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    'qq'        => [
        'client_id'     => env('QQ_CLIENT_ID'),
        'client_secret' => env('QQ_CLIENT_SECRET'),
        'redirect'      => env('QQ_REDIRECT_URI'),
    ],

    'weixinweb' => [
        'client_id'     => env('WEIXINWEB_CLIENT_ID'),
        'client_secret' => env('WEIXINWEB_CLIENT_SECRET'),
        'redirect'      => env('WEIXINWEB_REDIRECT_URI'),
        'auth_base_uri' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
    ],

    // 微信打开登录
    'weixin'    => [
        'client_id'     => env('WEIXIN_CLIENT_ID'),
        'client_secret' => env('WEIXIN_CLIENT_SECRET'),
        'redirect'      => env('WEIXIN_REDIRECT_URI'),
        'auth_base_uri' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
    ],

    'weibo'     => [
        'client_id'     => env('WEIBO_CLIENT_ID'),
        'client_secret' => env('WEIBO_CLIENT_SECRET'),
        'redirect'      => env('WEIBO_REDIRECT_URI'),
    ],

    'pick1'     => [
        'client_id'     => 'd0ee30f3-c72a-4013-a846-5de711805e0e',
        'client_secret' => '6ba95252-34c6-426b-a324-3642e2d5da39',
        'redirect'      => env('DEFARA_URL') . '/oauth/callback',
        'access_token'  => env('PICK1_URL') . '/oauth/access_token',
        'url'           => env('PICK1_URL') . '/oauth/authorize',
        'user'          => env('PICK1_URL') . '/oauth/user',
        // 'access_token' => 'https://www.pick1.cn/oauth/access_token',
        // 'user' => 'https://www.pick1.cn/oauth/user',
        // 'authorize' => 'https://www.pick1.cn/oauth/authorize',
    ],

    'paypal'    => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret'    => env('PAYPAL_SECRET'),
        'test_mode' => env('PAYPAL_TEST_MODE', true),
        'mode'      => env('PAYPAL_MODE', 'sandbox'),
    ],
];
