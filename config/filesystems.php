<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "s3", "rackspace"
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path().'/app',
        ],
        'log' => [
            'driver' => 'local',
            'root'   => storage_path().'/logs',
        ],
        'public' => [
            'driver' => 'local',
            'root'   => public_path(),
        ],
        's3' => [
            'driver' => 's3',
            'key'    => env('S3_KEY', 'AKIAJMYCDK6RG357FSIA'),
            'secret' => env('S3_SECRET', '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'),
            'region' => env('S3_REGION', 'ap-northeast-1'),
            'bucket' => env('S3_BUCKET', 'defara'),
        ],
        'qiniu' => [
            'driver'  => 'qiniu',
            'domains' => [
                'default'   => env('QINIU_DOMAIN'), //你的七牛域名
                'https'     => env('QINIU_HTTPS_DOMAIN'),
                'custom'    => '',
            ],
            'access_key'=> env('QINIU_ACCESS_KEY'),  //AccessKey
            'secret_key'=> env('QINIU_SECRET_KEY'),  //SecretKey
            'bucket'    => env('QINIU_BUCKET'),  //Bucket名字
            'notify_url'=> '',  //持久化处理回调地址
        ],

        'rackspace' => [
            'driver'    => 'rackspace',
            'username'  => 'your-username',
            'key'       => 'your-key',
            'container' => 'your-container',
            'endpoint'  => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'IAD',
            'url_type'  => 'publicURL'
        ],

    ],

];
