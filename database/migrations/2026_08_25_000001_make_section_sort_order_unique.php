<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $chapterIds = DB::table('sections')
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('chapter_id');

            foreach ($chapterIds as $chapterId) {
                $sectionIds = DB::table('sections')
                    ->where('chapter_id', $chapterId)
                    ->whereNull('deleted_at')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->pluck('id');

                foreach ($sectionIds as $index => $sectionId) {
                    DB::table('sections')
                        ->where('id', $sectionId)
                        ->update(['sort_order' => $index + 1]);
                }
            }
        });

        Schema::table('sections', function (Blueprint $table): void {
            // مقدار تولیدشده برای رکوردهای حذف‌شده NULL است؛ بنابراین
            // حذف نرم یک بخش، شمارهٔ آن را برای استفادهٔ دوباره آزاد می‌کند.
            $table->unsignedSmallInteger('active_sort_order')
                ->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN sort_order ELSE NULL END');

            $table->unique(
                ['chapter_id', 'active_sort_order'],
                'section_chapter_active_sort_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->dropUnique('section_chapter_active_sort_unique');
            $table->dropColumn('active_sort_order');
        });
    }
};
