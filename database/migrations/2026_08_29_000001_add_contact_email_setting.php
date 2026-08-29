<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'contact_email'],
            [
                'value' => config('mail.from.address', 'hello@example.com'),
                'group' => 'ارتباط با کاربران',
                'type' => 'email',
                'description' => 'ایمیل دریافت پیام‌های فرم تماس با ما (قابل تغییر توسط سوپر ادمین)',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'contact_email')->delete();
    }
};
