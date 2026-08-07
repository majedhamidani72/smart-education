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
        Schema::create('purchase_items', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | خرید
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | نوع آیتم
            |--------------------------------------------------------------------------
            */

            $table->enum('item_type', [

                'book',

                'lesson',

                'subscription',

                'package',

                'quiz',

            ]);

            /*
            |--------------------------------------------------------------------------
            | شناسه آیتم
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger(
                'item_id'
            );

            /*
            |--------------------------------------------------------------------------
            | عنوان آیتم
            |--------------------------------------------------------------------------
            */

            $table->string(
                'title'
            );

            /*
            |--------------------------------------------------------------------------
            | مبلغ‌ها
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger(
                'price'
            );

            $table->unsignedBigInteger(
                'discount_amount'
            )->default(0);

            $table->unsignedBigInteger(
                'final_price'
            );

            /*
            |--------------------------------------------------------------------------
            | تعداد
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger(
                'quantity'
            )->default(1);

            /*
            |--------------------------------------------------------------------------
            | توضیحات
            |--------------------------------------------------------------------------
            */

            $table->text(
                'notes'
            )->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['purchase_id'],
                'purchase_item_purchase_index'
            );

            $table->index(
                ['item_type', 'item_id'],
                'purchase_item_type_id_index'
            );

        });
    }

    /**
     * حذف جدول
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'purchase_items'
        );
    }
};
