<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| یادآوری انقضای اشتراک
|--------------------------------------------------------------------------
| هر روز ساعت ۱۰ صبح اجرا می‌شود.
*/
Schedule::command('subscriptions:notify-expiring')
    ->dailyAt('10:00');
