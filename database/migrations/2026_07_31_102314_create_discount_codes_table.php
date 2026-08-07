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
        Schema::create('discount_codes', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | اطلاعات کد تخفیف
            |--------------------------------------------------------------------------
            */

            $table->string('code')
                ->unique();

            $table->string('title');

            $table->text('description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | نوع تخفیف
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'fixed',

                'percent',

            ]);

            $table->unsignedBigInteger(
                'value'
            );

            $table->unsignedBigInteger(
                'max_discount'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | حداقل مبلغ خرید
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger(
                'minimum_purchase'
            )->default(0);

            /*
            |--------------------------------------------------------------------------
            | محدودیت استفاده
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger(
                'usage_limit'
            )->nullable();

            $table->unsignedInteger(
                'used_count'
            )->default(0);

            /*
            |--------------------------------------------------------------------------
            | هر کاربر چند بار؟
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger(
                'usage_per_user'
            )->default(1);

            /*
            |--------------------------------------------------------------------------
            | وضعیت
            |--------------------------------------------------------------------------
            */

            $table->boolean(
                'is_active'
            )->default(true);

            /*
            |--------------------------------------------------------------------------
            | تاریخ اعتبار
            |--------------------------------------------------------------------------
            */

            $table->timestamp(
                'starts_at'
            )->nullable();

            $table->timestamp(
                'expires_at'
            )->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['code']
            );

            $table->index(
                ['is_active']
            );

        });
    }

    /**
     * حذف جدول
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'discount_codes'
        );
    }
};
