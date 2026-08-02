<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {

            $table->id(); // شناسه خرید

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete(); // خریدار

            $table->string('invoice_number')
                ->unique(); // شماره فاکتور

            $table->decimal('total_amount', 12, 0); // مبلغ کل قبل از تخفیف

            $table->decimal('discount_amount', 12, 0)
                ->default(0); // مبلغ تخفیف

            $table->decimal('payable_amount', 12, 0); // مبلغ نهایی قابل پرداخت

            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'cancelled',
                'refunded'
            ])->default('pending'); // وضعیت پرداخت

            $table->enum('payment_method', [
                'zibal',
                'wallet',
                'admin'
            ])->nullable(); // روش پرداخت

            $table->timestamp('paid_at')
                ->nullable(); // زمان پرداخت موفق

            $table->timestamps(); // created_at و updated_at

            $table->softDeletes(); // حذف نرم

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
