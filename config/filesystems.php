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
        | فایل‌ها داخل storage/app/public ذخیره می‌شوند (استاندارد لاراول) و از
        | طریق symlink پوشه‌ی public/storage در دسترس عمومی قرار می‌گیرند.
        | این طراحی عمداً از public/ مستقیم استفاده نمی‌کند، چون public/ حاوی
        | index.php و فایل‌های اصلی کد است و باید همیشه از Git بیاید؛ در حالی که
        | storage/app باید روی یک دیسک دائمی (Persistent Disk) نگه داشته شود تا
        | با هر Deploy جدید پاک نشود.
        |--------------------------------------------------------------------------
        */

        'public' => [

            'driver' => 'local',

            'root' => storage_path(
                'app/public'
            ),

            'url' => rtrim(

                env(
                    'APP_URL',
                    'http://localhost'
                ),

                '/'

            ) . '/storage',

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
