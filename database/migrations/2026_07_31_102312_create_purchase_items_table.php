<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {

            $table->id(); // شناسه آیتم خرید

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnDelete(); // فاکتور مربوطه

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete(); // پلن خریداری شده

            $table->decimal('price', 12, 0); // قیمت اصلی پلن

            $table->decimal('discount_amount', 12, 0)
                ->default(0); // تخفیف این آیتم

            $table->decimal('final_price', 12, 0); // مبلغ نهایی پرداخت شده

            $table->timestamps(); // created_at و updated_at

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
