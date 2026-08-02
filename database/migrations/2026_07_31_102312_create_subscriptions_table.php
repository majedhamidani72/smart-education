<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {

            $table->id();
            // شناسه اشتراک


            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            // دانش‌آموز صاحب اشتراک


            $table->foreignId('purchase_item_id')
                ->constrained()
                ->cascadeOnDelete();
            // آیتم خریدی که باعث ایجاد اشتراک شده


            $table->dateTime('starts_at');
            // زمان شروع اشتراک


            $table->dateTime('expires_at');
            // زمان پایان اشتراک


            $table->enum('status', [
                'active',
                'expired',
                'cancelled'
            ])->default('active');
            // وضعیت اشتراک


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
