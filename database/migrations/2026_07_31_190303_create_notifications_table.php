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
        Schema::create('notifications', function (Blueprint $table) {

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
            | اطلاعات اعلان
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->text('message');

            /*
            |--------------------------------------------------------------------------
            | نوع اعلان
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'system',

                'purchase',

                'payment',

                'quiz',

                'lesson',

                'advertisement',

            ])->default('system');

            /*
            |--------------------------------------------------------------------------
            | لینک
            |--------------------------------------------------------------------------
            */

            $table->string('action_url')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | وضعیت خواندن
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_read')
                ->default(false);

            $table->timestamp('read_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | اطلاعات اضافی
            |--------------------------------------------------------------------------
            */

            $table->json('data')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('user_id');

            $table->index('type');

            $table->index('is_read');

        });
    }

    /**
     * حذف جدول
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'notifications'
        );
    }
};
