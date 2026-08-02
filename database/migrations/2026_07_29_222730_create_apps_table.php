<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id(); // شناسه اپلیکیشن

            $table->string('title'); // نام اپلیکیشن

            $table->string('image')->nullable();

            $table->string('slug')->unique(); // نام یکتا

            $table->string('package_name')->nullable()->unique(); // نام پکیج اندروید

            $table->text('description')->nullable(); // توضیحات

            $table->string('icon')->nullable(); // تصویر آیکون

            $table->string('current_version')->nullable(); // نسخه فعلی

            $table->boolean('force_update')->default(false); // اجبار بروزرسانی

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب نمایش

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
