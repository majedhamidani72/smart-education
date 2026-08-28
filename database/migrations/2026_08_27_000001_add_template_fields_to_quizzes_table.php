<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ترمیم اجرای نیمه‌کاره در MySQL (نسخه قبلی نام کلید خارجی
        // را «1» تولید می‌کرد). این ستون‌ها هنوز داده‌ای ندارند.
        if (Schema::hasColumn('quizzes', 'template_id')) {
            try { DB::statement('ALTER TABLE quizzes DROP FOREIGN KEY `1`'); } catch (\Throwable) {}
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn(['template_book_id', 'template_id', 'is_template']);
            });
        }

        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->index();
            $table->unsignedBigInteger('template_id')->nullable()->after('is_template')->index('quizzes_template_id_index');
            $table->unsignedBigInteger('template_book_id')->nullable()->after('template_id')->index('quizzes_template_book_id_index');
            $table->foreign('template_id', 'quizzes_template_id_foreign')->references('id')->on('quizzes')->nullOnDelete();
            $table->foreign('template_book_id', 'quizzes_template_book_id_foreign')->references('id')->on('books')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_book_id');
            $table->dropConstrainedForeignId('template_id');
            $table->dropColumn('is_template');
        });
    }
};
