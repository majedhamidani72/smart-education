<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_types', function (Blueprint $table) {
            $table->id(); // شناسه نوع محتوا

            $table->string('title'); // عنوان فارسی

            $table->string('slug')->unique(); // نام سیستمی

            $table->string('icon')->nullable(); // آیکون

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب نمایش

            $table->timestamps();

            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_types');
    }
};
