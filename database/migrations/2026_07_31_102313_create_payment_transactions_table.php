<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {

            $table->id(); // شناسه تراکنش

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnDelete(); // خرید مربوطه

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete(); // پرداخت کننده

            $table->string('gateway'); // درگاه پرداخت (zibal)

            $table->string('transaction_id')
                ->nullable(); // شناسه تراکنش در درگاه

            $table->string('reference_id')
                ->nullable(); // کد رهگیری بانک

            $table->decimal('amount',12,0); // مبلغ تراکنش

            $table->enum('status',[
                'pending',
                'paid',
                'failed',
                'cancelled',
                'refunded'
            ])->default('pending'); // وضعیت تراکنش

            $table->text('message')
                ->nullable(); // پیام دریافتی از درگاه

            $table->json('gateway_response')
                ->nullable(); // پاسخ کامل زیبال

            $table->timestamp('paid_at')
                ->nullable(); // زمان پرداخت موفق

            $table->timestamps(); // created_at و updated_at

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
