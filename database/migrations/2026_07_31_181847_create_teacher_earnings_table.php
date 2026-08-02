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
        Schema::create('teacher_earnings', function (Blueprint $table) {

    $table->id();

    $table->foreignId('teacher_id')
        ->constrained('users')
        ->cascadeOnDelete();
    // معلم دریافت کننده درآمد

    $table->foreignId('purchase_id')
        ->constrained()
        ->cascadeOnDelete();
    // خریدی که باعث درآمد شده

    $table->integer('amount');
    // مبلغ سهم معلم

    $table->integer('percentage');
    // درصد سهم معلم

    $table->enum('status', [
        'pending',
        'paid'
    ])->default('pending');
    // وضعیت پرداخت درآمد

    $table->timestamp('paid_at')
        ->nullable();
    // زمان تسویه

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_earnings');
    }
};
