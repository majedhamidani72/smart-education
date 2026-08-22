<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| اتصال مستقیم سوال به کتاب/فصل — نه فقط از طریق محتوا
|--------------------------------------------------------------------------
| مشکل: بانک سوالات سه‌سطحی فقط از طریق content_item_id می‌فهمید
| هر سوال مال کدام کتاب/فصل است. اگر آن فصل هنوز هیچ محتوای دیگری
| (ویدئو/گام‌به‌گام) نداشته باشد، سوال هیچ‌جا نمایش داده نمی‌شد —
| حتی وقتی معلم دقیقاً فصل را انتخاب کرده بود. این دقیقاً همان
| باگی است که قبلاً برای content_items با اضافه‌کردن ستون مستقیم
| chapter_id حل شد؛ همان راه‌حل، این‌بار برای questions.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {

            $table->foreignId('book_id')
                ->nullable()
                ->after('content_item_id')
                ->constrained('books')
                ->nullOnDelete();

            $table->foreignId('chapter_id')
                ->nullable()
                ->after('book_id')
                ->constrained('chapters')
                ->nullOnDelete();

            $table->foreignId('section_id')
                ->nullable()
                ->after('chapter_id')
                ->constrained('sections')
                ->nullOnDelete();

        });

        // بازسازی برای سوالات موجود، از روی content_item_id فعلی‌شان.
        DB::statement("
            UPDATE questions q
            INNER JOIN content_items ci ON ci.id = q.content_item_id
            INNER JOIN chapters ch ON ch.id = ci.chapter_id
            SET q.book_id = ch.book_id,
                q.chapter_id = ci.chapter_id,
                q.section_id = ci.section_id
            WHERE q.content_item_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {

            $table->dropConstrainedForeignId('section_id');

            $table->dropConstrainedForeignId('chapter_id');

            $table->dropConstrainedForeignId('book_id');

        });
    }
};
