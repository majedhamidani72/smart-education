<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id(); // شناسه درس

            $table->string('title'); // عنوان درس

            $table->string('slug')->unique(); // نام یکتا

            $table->text('description')->nullable(); // توضیحات

            $table->string('icon')->nullable(); // آیکون درس

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب نمایش

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
