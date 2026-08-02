<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id(); // شناسه محتوا

            $table->foreignId('section_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // بخش آموزشی

            $table->foreignId('content_type_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // نوع محتوا

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // سازنده محتوا

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete(); // مدیر بررسی‌کننده

            $table->string('title'); // عنوان محتوا

            $table->string('slug'); // نام یکتا

            $table->text('description')->nullable(); // توضیحات

            $table->unsignedSmallInteger('page_number')->nullable(); // شماره صفحه کتاب

            $table->string('thumbnail')->nullable(); // تصویر شاخص

            $table->boolean('is_free')->default(false); // رایگان یا پولی

            $table->string('status', 30)->default('draft'); // وضعیت محتوا

            $table->text('rejection_reason')->nullable(); // دلیل رد شدن

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب نمایش

            $table->timestamp('published_at')->nullable(); // زمان انتشار

            $table->timestamps();

            $table->softDeletes();

            $table->unique(
                ['section_id', 'slug'],
                'content_section_slug_unique'
            );

            $table->index(
                ['section_id', 'content_type_id', 'status'],
                'content_filter_index'
            );

            $table->index(
                ['is_free', 'status'],
                'content_access_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};
