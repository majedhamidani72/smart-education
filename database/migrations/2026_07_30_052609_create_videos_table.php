<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {

            $table->id();

            $table->foreignId('content_item_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            $table->string('directory');

            $table->string('filename')
                ->index();

            $table->string('original_name');

            $table->string('extension', 20);

            $table->string('mime_type', 100);

            $table->unsignedBigInteger('file_size');


            $table->unsignedInteger('duration')
                ->nullable();

            $table->string('quality', 30)
                ->nullable();

            $table->string('thumbnail_path')
                ->nullable();


            $table->unsignedBigInteger('views_count')
                ->default(0);

            $table->boolean('download_allowed')
                ->default(false);


            $table->string('processing_status')
                ->default('pending');


            $table->timestamp('approved_at')
                ->nullable();


            $table->text('rejected_reason')
                ->nullable();


            $table->timestamps();

            $table->softDeletes();


            $table->index('processing_status');

            $table->index('uploaded_by');

            $table->index('approved_by');

            $table->index('views_count');

            $table->index('content_item_id');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
