<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {

            $table->id();

            // کاربر (در اولین ورود ممکن است هنوز وجود نداشته باشد)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // شماره موبایل
            $table->string('mobile', 11)
                ->index();

            // کد یکبار مصرف
            $table->string('code', 6);

            // توکن موقت ورود
            $table->string('login_token', 64)
                ->index();

            // هدف OTP
            $table->enum('purpose', [
                'login',
                'reset_password',
            ]);

            // تعداد دفعات ورود اشتباه
            $table->unsignedTinyInteger('attempts')
                ->default(0);

            // تایید شده؟
            $table->boolean('is_verified')
                ->default(false);

            // زمان انقضا
            $table->timestamp('expires_at');

            // زمان تایید
            $table->timestamp('verified_at')
                ->nullable();

            // زمان استفاده
            $table->timestamp('used_at')
                ->nullable();

            // IP
            $table->string('ip_address', 45)
                ->nullable();

            // مرورگر
            $table->text('user_agent')
                ->nullable();

            $table->timestamps();

            $table->index(
                ['mobile', 'purpose'],
                'otp_mobile_purpose_index'
            );

            $table->index(
                ['login_token', 'is_verified'],
                'otp_login_token_verified_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
