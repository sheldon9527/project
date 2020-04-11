<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => 'gd',

    'defaultImg' => '/assets/default/defaultAvatar.jpg',

    'configImg' => [
        'commodity' => 'images/commodity.jpg',
        'designer' => 'images/designer.jpg',
        'maker' => 'images/maker.jpg',
        'avatar' => 'css/imgs/user-face.jpg',
        'brand' => 'images/brand.jpg',
        'showroom' => 'images/new-showroom.jpg',
        'adv' => 'images/adv.jpg'
        ]
);
