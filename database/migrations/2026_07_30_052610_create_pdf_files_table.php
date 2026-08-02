<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pdf_files', function (Blueprint $table) {

            $table->id();

            $table->foreignId('content_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title',150); // عنوان PDF

            $table->string('file'); // فایل PDF

            $table->unsignedInteger('file_size')->nullable(); // حجم فایل

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_files');
    }
};
