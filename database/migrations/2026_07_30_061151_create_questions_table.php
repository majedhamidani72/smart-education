<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id(); // شناسه سؤال

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // سازنده سؤال

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete(); // مدیر بررسی‌کننده

            $table->text('question_text')->nullable(); // متن سؤال

            $table->string('image_path')->nullable(); // تصویر سؤال

            $table->text('explanation')->nullable(); // توضیح پاسخ صحیح

            $table->string('explanation_image_path')->nullable(); // تصویر توضیح پاسخ

            $table->string('difficulty', 20)->default('easy'); // سطح سختی

            $table->unsignedSmallInteger('default_score')->default(1); // امتیاز پیش‌فرض

            $table->string('status', 30)->default('draft'); // وضعیت سؤال

            $table->text('rejection_reason')->nullable(); // دلیل رد سؤال

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->timestamps();

            $table->softDeletes();

            $table->index(
                ['difficulty', 'status', 'is_active'],
                'question_filter_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
