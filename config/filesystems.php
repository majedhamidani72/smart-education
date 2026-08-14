<?php

return [

    'default' => env(
        'FILESYSTEM_DISK',
        'local'
    ),


    'disks' => [


        'local' => [

            'driver' => 'local',

            'root' => storage_path(
                'app'
            ),

            'serve' => true,

            'throw' => true,

            'report' => false,

        ],



        /*
        |--------------------------------------------------------------------------
        | Public Files
        |--------------------------------------------------------------------------
        | فایل‌های پروژه مستقیم داخل public ذخیره می‌شوند
        |--------------------------------------------------------------------------
        */

        'public' => [

            'driver' => 'local',

            'root' => public_path(),

            'url' => rtrim(

                env(
                    'APP_URL',
                    'http://localhost'
                ),

                '/'

            ),

            'visibility' => 'public',

            'throw' => true,

            'report' => false,

        ],



        's3' => [

            'driver' => 's3',

            'key' => env(
                'AWS_ACCESS_KEY_ID'
            ),

            'secret' => env(
                'AWS_SECRET_ACCESS_KEY'
            ),

            'region' => env(
                'AWS_DEFAULT_REGION'
            ),

            'bucket' => env(
                'AWS_BUCKET'
            ),

            'url' => env(
                'AWS_URL'
            ),

            'endpoint' => env(
                'AWS_ENDPOINT'
            ),

            'use_path_style_endpoint' => env(
                'AWS_USE_PATH_STYLE_ENDPOINT',
                false
            ),

            'throw' => true,

            'report' => false,

        ],

    ],



    'links' => [

        public_path('storage') => storage_path(
            'app/public'
        ),

    ],

];
