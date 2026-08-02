<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id(); // شناسه پایه

            $table->string('title'); // عنوان پایه

            $table->string('slug')->unique(); // نام یکتا

            $table->unsignedTinyInteger('grade_number')->unique(); // شماره پایه

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب نمایش

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
