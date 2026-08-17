<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            $table->foreignId('book_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Chapter Information
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->string('slug');

            $table->string('thumbnail')
                ->nullable();

            $table->unsignedSmallInteger('sort_order')
                ->default(1);

            $table->boolean('is_active')
                ->default(true);

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

            $table->unique(
                [
                    'book_id',
                    'slug',
                ],
                'chapter_book_slug_unique'
            );

            $table->index(
                [
                    'book_id',
                    'sort_order',
                ],
                'chapter_book_sort_index'
            );

            $table->index(
                [
                    'book_id',
                    'is_active',
                ],
                'chapter_filter_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
