<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| پیشنهاد مطالعه به‌صورت متن آزاد (نه انتخاب از لیست)
|--------------------------------------------------------------------------
| طبق تصمیم پروژه، معلم خودش این متن را تایپ می‌کند (مثلاً «صفحه‌ی
| ۴۵ کتاب را دوباره بخوان» یا «کلیپ فصل ۳ بخش ۲ را ببین») — نه
| این‌که از یک لیست محتوای مشخص انتخاب کند.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {

            $table->dropConstrainedForeignId('recommended_content_item_id');

            $table->text('recommendation_text')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {

            $table->dropColumn('recommendation_text');

            $table->foreignId('recommended_content_item_id')
                ->nullable()
                ->constrained('content_items')
                ->nullOnDelete();

        });
    }
};
