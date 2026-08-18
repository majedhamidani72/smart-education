<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| افزودن عنوان به صفحات گام‌به‌گام
|--------------------------------------------------------------------------
| فرم «ایجاد محتوا» از قبل یک فیلد «عنوان صفحه» برای هر گام
| می‌گرفت، اما جدول step_by_step_pages اصلاً ستونی برای ذخیره‌ی
| آن نداشت — یعنی این اطلاعات همیشه گم می‌شد. این migration آن
| ستون را اضافه می‌کند.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('step_by_step_pages', function (Blueprint $table) {

            $table->string('title')
                ->nullable()
                ->after('step_by_step_id');

        });
    }

    public function down(): void
    {
        Schema::table('step_by_step_pages', function (Blueprint $table) {

            $table->dropColumn('title');

        });
    }
};
