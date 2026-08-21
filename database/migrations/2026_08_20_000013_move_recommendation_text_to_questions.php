<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| پیشنهاد مطالعه: یکی برای کل سوال، نه برای هر گزینه
|--------------------------------------------------------------------------
| طبق تصمیم پروژه، نیازی نیست هر گزینه‌ی غلط پیشنهاد جدا داشته
| باشد — یک پیشنهاد برای کل سوال کافی است.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {

            $table->dropColumn('recommendation_text');

        });

        Schema::table('questions', function (Blueprint $table) {

            $table->text('recommendation_text')
                ->nullable()
                ->after('explanation_image_path');

        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {

            $table->dropColumn('recommendation_text');

        });

        Schema::table('question_options', function (Blueprint $table) {

            $table->text('recommendation_text')->nullable();

        });
    }
};
