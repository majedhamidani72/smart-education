<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id(); // شناسه تلاش آزمون

            $table->foreignId('quiz_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // آزمون

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // دانش‌آموز

            $table->unsignedInteger('total_score')->default(0); // کل امتیاز آزمون

            $table->unsignedInteger('earned_score')->default(0); // امتیاز کسب‌شده

            $table->decimal('percentage', 5, 2)->default(0); // درصد نتیجه

            $table->unsignedSmallInteger('correct_answers_count')->default(0); // پاسخ صحیح

            $table->unsignedSmallInteger('wrong_answers_count')->default(0); // پاسخ غلط

            $table->unsignedSmallInteger('unanswered_count')->default(0); // بدون پاسخ

            $table->string('status', 20)->default('started'); // وضعیت تلاش

            $table->timestamp('started_at')->nullable(); // زمان شروع

            $table->timestamp('finished_at')->nullable(); // زمان پایان

            $table->unsignedInteger('duration_seconds')->nullable(); // مدت شرکت

            $table->timestamps();

            $table->index(
                ['user_id', 'quiz_id'],
                'user_quiz_attempt_index'
            );

            $table->index(
                ['status', 'finished_at'],
                'quiz_attempt_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
