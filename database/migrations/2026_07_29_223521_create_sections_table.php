<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id(); // شناسه بخش

            $table->foreignId('chapter_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // فصل

            $table->string('title'); // عنوان بخش

            $table->string('slug'); // نام یکتا

            $table->text('description')->nullable(); // توضیحات

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب بخش

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->timestamps();

            $table->softDeletes();

            $table->unique(
                ['chapter_id', 'slug'],
                'section_chapter_slug_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
