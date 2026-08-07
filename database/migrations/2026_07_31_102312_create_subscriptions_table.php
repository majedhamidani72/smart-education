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
        Schema::create('subscriptions', function (Blueprint $table) {

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
            | خرید
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | پلن اشتراک
            |--------------------------------------------------------------------------
            */

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | وضعیت اشتراک
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'active',

                'expired',

                'cancelled',

            ])->default('active');

            /*
            |--------------------------------------------------------------------------
            | تاریخ شروع و پایان
            |--------------------------------------------------------------------------
            */

            $table->dateTime('starts_at');

            $table->dateTime('expires_at');

            /*
            |--------------------------------------------------------------------------
            | زمان لغو
            |--------------------------------------------------------------------------
            */

            $table->dateTime('cancelled_at')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | ایندکس‌ها
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['user_id', 'status'],
                'subscription_user_status_index'
            );

            $table->index(
                'expires_at',
                'subscription_expire_index'
            );

        });
    }

    /**
     * حذف جدول
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'subscriptions'
        );
    }
};
