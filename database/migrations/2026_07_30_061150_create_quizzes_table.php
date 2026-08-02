<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id(); // شناسه آزمون

            $table->nullableMorphs('quizable'); // بخش، فصل یا کتاب

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // سازنده آزمون

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete(); // مدیر بررسی‌کننده

            $table->string('title'); // عنوان آزمون

            $table->text('description')->nullable(); // توضیحات آزمون

            $table->unsignedSmallInteger('questions_count')->default(10); // تعداد سؤال نمایشی

            $table->unsignedSmallInteger('time_limit')->nullable(); // زمان آزمون به دقیقه

            $table->unsignedTinyInteger('passing_percentage')->default(50); // درصد قبولی

            $table->unsignedSmallInteger('max_attempts')->nullable(); // حداکثر دفعات شرکت

            $table->boolean('randomize_questions')->default(true); // ترتیب تصادفی سؤال‌ها

            $table->boolean('randomize_options')->default(true); // ترتیب تصادفی گزینه‌ها

            $table->boolean('show_result')->default(true); // نمایش نتیجه

            $table->boolean('show_correct_answers')->default(false); // نمایش پاسخ صحیح

            $table->boolean('is_free')->default(false); // رایگان یا پولی

            $table->string('status', 30)->default('draft'); // وضعیت آزمون

            $table->text('rejection_reason')->nullable(); // دلیل رد آزمون

            $table->timestamp('published_at')->nullable(); // زمان انتشار

            $table->timestamps();

            $table->softDeletes();

            $table->index(
                ['status', 'is_free'],
                'quiz_status_access_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
