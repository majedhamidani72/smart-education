<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| پر کردن ستون «group» موجود روی جدول settings
|--------------------------------------------------------------------------
| این ستون از اول جدول وجود داشت ولی هیچ‌وقت واقعاً پر نمی‌شد —
| برای همین همه‌ی تنظیمات (قرارداد، کارمزد درگاه، امنیت، برنامه)
| توی یک لیست تخت و بدون دسته‌بندی نمایش داده می‌شدند.
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'like', '%agreement%')
            ->update(['group' => 'قرارداد و شرایط استفاده']);

        DB::table('settings')
            ->where('key', 'like', 'gateway_fee_%')
            ->update(['group' => 'کارمزد درگاه‌ها و سهم معلم']);

        DB::table('settings')
            ->where('key', 'default_teacher_commission_percentage')
            ->update(['group' => 'کارمزد درگاه‌ها و سهم معلم']);

        DB::table('settings')
            ->whereIn('key', ['password_min_length', 'force_change_password'])
            ->update(['group' => 'امنیت']);

        DB::table('settings')
            ->whereIn('key', ['app_name', 'app_logo', 'video_max_size', 'android_min_version', 'force_update'])
            ->update(['group' => 'برنامه و برنامک']);
    }

    public function down(): void
    {
        DB::table('settings')->update(['group' => 'general']);
    }
};
