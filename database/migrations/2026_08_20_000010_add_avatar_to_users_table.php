<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| عکس پروفایل عمومی
|--------------------------------------------------------------------------
| قبلاً عکس پروفایل فقط برای معلم (روی جدول جدای teacher_profiles)
| وجود داشت. این ستون عمومی روی خودِ users است تا ادمین هم بتواند
| عکس پروفایل داشته باشد، بدون نیاز به جدول‌های جدا برای هر نقش.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('avatar')->nullable()->after('mobile');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('avatar');

        });
    }
};
