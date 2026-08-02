<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // شناسه کاربر

            $table->string('name')->nullable(); // نام کاربر

            $table->string('mobile', 11)->unique(); // شماره موبایل

            $table->string('password')->nullable(); // رمز معلم و مدیر

            $table->boolean('must_change_password')->default(false); // اجبار تغییر رمز اولیه

            $table->timestamp('phone_verified_at')->nullable(); // زمان تأیید موبایل

            $table->boolean('is_active')->default(true); // فعال یا مسدود

            $table->timestamp('last_login_at')->nullable(); // آخرین ورود

            $table->rememberToken();

            $table->timestamps();

            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('mobile')->primary(); // شماره موبایل

            $table->string('token'); // توکن بازیابی

            $table->timestamp('created_at')->nullable(); // زمان ایجاد
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // شناسه نشست

            $table->foreignId('user_id')
                ->nullable()
                ->index(); // کاربر نشست

            $table->string('ip_address', 45)->nullable(); // آدرس IP

            $table->text('user_agent')->nullable(); // اطلاعات دستگاه

            $table->longText('payload'); // اطلاعات نشست

            $table->integer('last_activity')->index(); // آخرین فعالیت
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
