<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| درصد سهم معلم از فروش
|--------------------------------------------------------------------------
| طبق تصمیم پروژه، هر معلم درصد جداگانه‌ای دارد (نه یک عدد ثابت
| برای همه)؛ این درصد مستقیماً روی همان تخصیص معلم↔کتاب تنظیم
| می‌شود.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {

            $table->unsignedTinyInteger('commission_percentage')
                ->default(30)
                ->after('book_id');

        });
    }

    public function down(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {

            $table->dropColumn('commission_percentage');

        });
    }
};
