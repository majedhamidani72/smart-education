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
        Schema::create('system_logs', function (Blueprint $table) {

    $table->id();
    // شناسه لاگ


    $table->foreignId('user_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();
    // کاربری که عملیات را انجام داده


    $table->string('action');
    // نوع عملیات
    // مثال: delete_content


    $table->text('description')
        ->nullable();
    // توضیحات عملیات


    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
