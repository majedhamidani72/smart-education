<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| پروفایل معلم
|--------------------------------------------------------------------------
| اطلاعاتی که خودِ معلم (نه ادمین) پر می‌کند: عکس پروفایل، سابقه‌ی
| خدمت در آموزش‌وپرورش، و شماره کارت برای تسویه‌حساب درآمدش. این
| اطلاعات از جدول users جدا نگه داشته می‌شود چون مخصوص نقش معلم
| است، نه هر کاربری.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('photo')->nullable();

            $table->unsignedTinyInteger('years_of_experience')->nullable();

            $table->string('card_number', 19)->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
