<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('powerpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained('apps')->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('preview_image')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('discount_price')->nullable();
            $table->unsignedInteger('slides_count')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['book_id', 'chapter_id', 'is_active']);
        });

        DB::statement("ALTER TABLE purchase_items MODIFY item_type ENUM('book','lesson','subscription','package','quiz','grade','powerpoint') NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('powerpoints');
        DB::statement("ALTER TABLE purchase_items MODIFY item_type ENUM('book','lesson','subscription','package','quiz','grade') NOT NULL");
    }
};
