<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_attempts', function (Blueprint $table) {
            $table->id(); // شناسه پاسخ سؤال

            $table->foreignId('quiz_attempt_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // تلاش آزمون

            $table->foreignId('question_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // سؤال

            $table->foreignId('question_option_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete(); // گزینه انتخاب‌شده

            $table->boolean('is_correct')->nullable(); // صحیح یا غلط

            $table->unsignedSmallInteger('score_awarded')->default(0); // امتیاز کسب‌شده

            $table->json('question_snapshot')->nullable(); // نسخه سؤال هنگام آزمون

            $table->json('options_snapshot')->nullable(); // نسخه گزینه‌ها هنگام آزمون

            $table->timestamp('answered_at')->nullable(); // زمان پاسخ

            $table->timestamps();

            $table->unique(
                ['quiz_attempt_id', 'question_id'],
                'attempt_question_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_attempts');
    }
};
