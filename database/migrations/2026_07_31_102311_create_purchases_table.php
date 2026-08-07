<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * اجرای Migration
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | کاربر
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | شماره فاکتور
            |--------------------------------------------------------------------------
            */

            $table->string('invoice_number')
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | مبلغ‌ها
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('total_amount');

            $table->unsignedBigInteger('discount_amount')
                ->default(0);

            $table->unsignedBigInteger('payable_amount');

            /*
            |--------------------------------------------------------------------------
            | وضعیت سفارش
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'paid',

                'cancelled',

                'refunded',

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | زمان پرداخت
            |--------------------------------------------------------------------------
            */

            $table->timestamp('paid_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | توضیحات ادمین
            |--------------------------------------------------------------------------
            */

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['user_id', 'status'],
                'purchase_user_status_index'
            );

            $table->index(
                ['invoice_number'],
                'purchase_invoice_index'
            );

        });
    }

    /**
     * حذف جدول
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'purchases'
        );
    }
};
