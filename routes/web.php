<?php

use Illuminate\Support\Facades\Route;

Route::get('/php-test', function () {
    return [
        'php_binary'    => PHP_BINARY,
        'php_sapi'      => PHP_SAPI,
        'loaded_ini'    => php_ini_loaded_file(),
        'scan_dir'      => php_ini_scanned_files(),
        'extension_dir' => ini_get('extension_dir'),
        'intl_loaded'   => extension_loaded('intl'),
    ];
});
