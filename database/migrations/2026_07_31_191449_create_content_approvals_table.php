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
        Schema::create('content_approvals', function (Blueprint $table) {

    $table->id();

    $table->foreignId('content_item_id')
        ->constrained()
        ->cascadeOnDelete();
    // محتوای مورد بررسی


    $table->foreignId('admin_id')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();
    // ادمینی که بررسی کرده


    $table->enum('status', [
        'pending',
        'approved',
        'rejected'
    ])->default('pending');
    // وضعیت تایید


    $table->text('note')
        ->nullable();
    // توضیحات ادمین


    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_approvals');
    }
};
