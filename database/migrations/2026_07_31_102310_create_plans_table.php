<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {

            $table->id(); // شناسه پلن

            $table->string('title'); // عنوان پلن

            $table->text('description')->nullable(); // توضیحات

            $table->morphs('planable'); // محصول قابل فروش

            $table->decimal('price',12,0); // قیمت اصلی

            $table->decimal('discount_price',12,0)->nullable(); // قیمت با تخفیف

            $table->enum('purchase_type',[
                'one_time',
                'subscription'
            ]); // نوع خرید

            $table->unsignedSmallInteger('duration_days')
                ->nullable(); // مدت اشتراک

            $table->boolean('is_active')
                ->default(true); // فعال

            $table->unsignedSmallInteger('sort_order')
                ->default(1); // ترتیب نمایش

            $table->timestamp('starts_at')
                ->nullable(); // شروع فروش

            $table->timestamp('expires_at')
                ->nullable(); // پایان فروش

            $table->timestamps();

            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
