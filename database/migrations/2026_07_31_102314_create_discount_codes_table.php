<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {

            $table->id(); // شناسه کد تخفیف

            $table->string('code')->unique(); // کد تخفیف

            $table->string('title'); // عنوان تخفیف

            $table->enum('type', [
                'fixed',
                'percent'
            ]); // نوع تخفیف

            $table->decimal('value', 12, 0); // مقدار تخفیف

            $table->decimal('max_discount', 12, 0)
                ->nullable(); // حداکثر مبلغ تخفیف

            $table->decimal('minimum_purchase', 12, 0)
                ->default(0); // حداقل مبلغ خرید

            $table->unsignedInteger('usage_limit')
                ->nullable(); // حداکثر تعداد استفاده

            $table->unsignedInteger('used_count')
                ->default(0); // تعداد استفاده شده

            $table->dateTime('starts_at')
                ->nullable(); // شروع اعتبار

            $table->dateTime('expires_at')
                ->nullable(); // پایان اعتبار

            $table->boolean('is_active')
                ->default(true); // فعال یا غیرفعال

            $table->timestamps(); // created_at و updated_at

            $table->softDeletes(); // حذف نرم

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
