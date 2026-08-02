<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id(); // شناسه فصل

            $table->foreignId('book_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // کتاب

            $table->string('title'); // عنوان فصل

            $table->string('slug'); // نام یکتا

            $table->text('description')->nullable(); // توضیحات

            $table->string('thumbnail')->nullable(); // تصویر فصل

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب فصل

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->timestamps();

            $table->softDeletes();

            $table->unique(
                ['book_id', 'slug'],
                'chapter_book_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
