<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| اضافه کردن chapter_id مستقل به content_items
|--------------------------------------------------------------------------
| قبلاً محتوا فقط از طریق section_id به فصل/کتاب/درس/پایه وصل
| می‌شد. چون «بخش» توی فرم اختیاری است، هر محتوایی که بدون بخش
| ساخته شده بود، هیچ راهی برای رسیدن به فصل/کتاب/درس/پایه‌اش
| نداشت (نه فقط فیلترها، هیچ گزارش یا ویژگی دیگری هم که به این
| زنجیره نیاز دارد کار نمی‌کرد). این migration:
|   ۱) ستون chapter_id را اضافه می‌کند (مستقل از section_id،
|      همیشه ذخیره می‌شود چون فصل هیچ‌وقت اختیاری نبوده)
|   ۲) رکوردهای موجود را از روی section_id.chapter_id پر می‌کند
|      تا داده‌های قبلی هم از این به بعد قابل فیلتر باشند.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_items', function (Blueprint $table) {

            $table->foreignId('chapter_id')
                ->nullable()
                ->after('section_id')
                ->constrained('chapters')
                ->nullOnDelete();

        });

        // بازپرکردن رکوردهای موجود از روی section_id
        DB::statement('
            UPDATE content_items
            INNER JOIN sections ON sections.id = content_items.section_id
            SET content_items.chapter_id = sections.chapter_id
            WHERE content_items.section_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('content_items', function (Blueprint $table) {

            $table->dropConstrainedForeignId('chapter_id');

        });
    }
};
