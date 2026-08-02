<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id(); // شناسه ویدئو

            $table->foreignId('content_item_id')
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // محتوای اصلی

            $table->string('storage_disk', 50)->default('public'); // دیسک ذخیره‌سازی

            $table->string('file_path'); // مسیر فایل ویدئو

            $table->string('original_name')->nullable(); // نام اصلی فایل

            $table->string('mime_type', 100)->nullable(); // نوع فایل

            $table->unsignedBigInteger('file_size')->nullable(); // حجم فایل به بایت

            $table->unsignedInteger('duration')->nullable(); // مدت ویدئو به ثانیه

            $table->string('quality', 20)->nullable(); // کیفیت مانند 720p

            $table->string('thumbnail_path')->nullable(); // تصویر بندانگشتی

            $table->unsignedBigInteger('views_count')->default(0); // تعداد بازدید

            $table->boolean('download_allowed')->default(false); // اجازه دانلود

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
