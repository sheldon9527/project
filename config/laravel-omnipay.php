<?php

return [

    // 默认支付网关
    'default' => 'alipay',

    // 各个支付网关配置
    'gateways' => [
        'paypal' => [
            //'driver' => 'PayPal_Express',
            'driver' => 'PayPal_Rest',
            'options' => [
                'solutionType' => '',
                'landingPage' => '',
                'headerImageUrl' => '',
            ],
        ],

        'alipay' => [
            'driver' => 'Alipay_Express',
            'options' => [
                'partner' => '2088911898349013',
                'key' => '42und819ealxnsaxgtyq123w2odncpb4',
                'sellerEmail' => 'howard.li@defara.com',
                'signType'    => 'MD5',
                'returnUrl' => env('ALIPAY_RETURN_URL', 'http://api.defara.com/api/alipay/return'),
                'notifyUrl' => env('ALIPAY_NOTIFY_URL', 'http://api.defara.com/api/alipay/notify'),
            ],
            'prefix' => env('ALIPAY_PREFIX', 'DEFARA')
        ],
    ],
];
