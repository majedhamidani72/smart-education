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
        Schema::create('wallet_transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | کیف پول
            |--------------------------------------------------------------------------
            */

            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

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
            | نوع تراکنش
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'deposit',

                'withdraw',

                'refund',

                'purchase',

                'reward',

                'gift',

                'adjustment',

            ]);

            /*
            |--------------------------------------------------------------------------
            | مبلغ
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger(
                'amount'
            );

            /*
            |--------------------------------------------------------------------------
            | موجودی قبل و بعد
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger(
                'balance_before'
            );

            $table->unsignedBigInteger(
                'balance_after'
            );

            /*
            |--------------------------------------------------------------------------
            | وضعیت
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'success',

                'failed',

                'cancelled',

            ])->default('success');

            /*
            |--------------------------------------------------------------------------
            | توضیحات
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | اطلاعات تکمیلی
            |--------------------------------------------------------------------------
            */

            $table->json('meta')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | ایندکس‌ها
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['wallet_id']
            );

            $table->index(
                ['user_id']
            );

            $table->index(
                ['type']
            );

            $table->index(
                ['status']
            );

        });
    }

    /**
     * حذف جدول
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'wallet_transactions'
        );
    }
};
