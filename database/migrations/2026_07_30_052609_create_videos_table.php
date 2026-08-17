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

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('content_item_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | File Information
            |--------------------------------------------------------------------------
            */

            $table->string('directory');

            $table->string('filename');

            $table->string('original_name');

            $table->string('extension', 20);

            $table->string('mime_type', 100);

            $table->unsignedBigInteger('file_size');

            /*
            |--------------------------------------------------------------------------
            | Video Information
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('duration')->nullable();

            $table->string('quality', 30)->nullable();

            $table->string('thumbnail_path')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Statistics
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('views_count')
                ->default(0);

            $table->boolean('download_allowed')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique('content_item_id');

            $table->index([
                'uploaded_by',
                'views_count',
            ]);

            $table->index([
                'duration',
                'quality',
            ]);

            $table->index('filename');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
