<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| پیشنهاد مطالعه بر اساس گزینه‌ی اشتباه
|--------------------------------------------------------------------------
| طبق طرح اولیه‌ی پروژه: اگر دانش‌آموز جواب اشتباه داد، باید بتوان
| او را به یک محتوای خاص (کلیپ، گام‌به‌گام، یا صفحه‌ی مشخصی از
| کتاب — که همه در قالب همان ContentItem مدل شده‌اند) هدایت کرد.
| این تصمیم به‌ازای هر «گزینه» گرفته می‌شود (نه کل سوال)، چون
| گزینه‌های غلط مختلف می‌توانند نشانه‌ی نقاط ضعف متفاوتی باشند.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {

            $table->foreignId('recommended_content_item_id')
                ->nullable()
                ->after('is_correct')
                ->constrained('content_items')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {

            $table->dropConstrainedForeignId('recommended_content_item_id');

        });
    }
};
