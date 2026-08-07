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
        Schema::create('advertisements', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | اطلاعات تبلیغ
            |--------------------------------------------------------------------------
            */

            $table->string('title');
            // عنوان تبلیغ

            $table->string('image');
            // تصویر تبلیغ

            $table->string('link')
                ->nullable();
            // لینک مقصد

            $table->text('description')
                ->nullable();
            // توضیحات

            /*
            |--------------------------------------------------------------------------
            | محل نمایش
            |--------------------------------------------------------------------------
            */

            $table->enum('position', [

                'home',

                'book',

                'lesson',

                'quiz',

                'profile',

                'popup',

            ]);
            // محل نمایش

            /*
            |--------------------------------------------------------------------------
            | ترتیب نمایش
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sort_order')
                ->default(1);

            /*
            |--------------------------------------------------------------------------
            | وضعیت
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | زمان نمایش
            |--------------------------------------------------------------------------
            */

            $table->timestamp('starts_at')
                ->nullable();

            $table->timestamp('expires_at')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('position');

            $table->index('is_active');

            $table->index('sort_order');

        });
    }

    /**
     * حذف جدول
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'advertisements'
        );
    }
};
