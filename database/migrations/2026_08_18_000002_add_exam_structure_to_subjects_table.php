<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| نوع ساختار آزمون هر درس
|--------------------------------------------------------------------------
| برخی درس‌ها (مثل ریاضی) بر اساس فصل/بخش سنجیده می‌شوند؛ برخی
| دیگر (مثل فارسی، مطالعات) طبق آیین‌نامه‌ی رسمی وزارت آموزش و
| پرورش بر اساس «درس به درس» + «نوبت اول (نصف کتاب) / نوبت دوم
| (کل کتاب)» سنجیده می‌شوند. این ستون مشخص می‌کند فرم آزمون برای
| این درس کدام حالت را نشان بدهد.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {

            $table->string('exam_structure', 20)
                ->default('chapter_section')
                ->after('title');

        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {

            $table->dropColumn('exam_structure');

        });
    }
};
