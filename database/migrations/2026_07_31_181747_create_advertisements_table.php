<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {

        $table->id();

        $table->string('title');
        // عنوان تبلیغ

        $table->string('image');
        // مسیر تصویر بنر

        $table->string('link')->nullable();
        // لینک مقصد

        $table->string('position')->nullable();
        // محل نمایش مثل home_top

        $table->dateTime('start_date')->nullable();
        // شروع نمایش

        $table->dateTime('end_date')->nullable();
        // پایان نمایش

        $table->boolean('is_active')->default(true);
        // فعال یا غیرفعال بودن

        $table->timestamps();

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
