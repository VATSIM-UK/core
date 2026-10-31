<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

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

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

        // Private bucket for user and system uploads. Works with Amazon S3
        // and S3-compatible providers (R2, Spaces, Hetzner, MinIO, ...);
        // set AWS_ENDPOINT and AWS_USE_PATH_STYLE_ENDPOINT for the latter.
        // UPLOADS_DRIVER=local is a development-only convenience.
        'uploads' => env('UPLOADS_DRIVER', 's3') === 'local'
            ? [
                'driver' => 'local',
                'root' => storage_path('app/uploads'),
                'url' => env('APP_URL').'/storage/uploads',
                'visibility' => 'private',
                'serve' => true,
                'throw' => true,
            ]
            : [
                'driver' => 's3',
                'key' => env('AWS_ACCESS_KEY_ID', env('AWS_KEY')),
                'secret' => env('AWS_SECRET_ACCESS_KEY', env('AWS_SECRET')),
                'region' => env('AWS_DEFAULT_REGION', env('AWS_REGION', 'eu-west-2')),
                'bucket' => env('AWS_BUCKET'),
                'endpoint' => env('AWS_ENDPOINT'),
                'use_path_style_endpoint' => (bool) env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                'visibility' => 'private',
                'throw' => true,
            ],

    ],

];
