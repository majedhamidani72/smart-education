<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| حذف تنظیم قدیمی و بلااستفاده‌ی «درصد سهم معلم»
|--------------------------------------------------------------------------
| teacher_share_percent یک تنظیم قدیمی (از قبل از ساخت سیستم
| درآمد معلمان فعلی) بود که هیچ‌جای کد صدا زده نمی‌شد — دقیقاً
| همان «۷۰»ی که باعث سردرگمی شده بود، جدا از تنظیم واقعی
| default_teacher_commission_percentage.
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'teacher_share_percent')
            ->delete();
    }

    public function down(): void
    {
        DB::table('settings')->insert([
            'key' => 'teacher_share_percent',
            'value' => '70',
            'description' => 'درصد سهم معلم',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
