<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id(); // شناسه کتاب

            $table->foreignId('grade_subject_id')
                ->constrained('grade_subject')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // پایه و درس کتاب

            $table->string('title'); // عنوان کتاب

            $table->string('slug'); // نام یکتا

            $table->string('cover')->nullable(); // تصویر جلد

            $table->string('academic_year', 20)->nullable(); // سال تحصیلی

            $table->unsignedSmallInteger('pages_count')->nullable(); // تعداد صفحات

            $table->text('description')->nullable(); // توضیحات

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب نمایش

            $table->timestamps();

            $table->softDeletes();

            $table->unique(
                ['grade_subject_id', 'slug'],
                'book_grade_subject_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
