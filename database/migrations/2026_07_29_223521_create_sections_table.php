<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            $table->foreignId('chapter_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Section Information
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->string('slug');

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
                    'chapter_id',
                    'slug',
                ],
                'section_chapter_slug_unique'
            );

            $table->index(
                [
                    'chapter_id',
                    'sort_order',
                ],
                'section_chapter_sort_index'
            );

            $table->index(
                [
                    'chapter_id',
                    'is_active',
                ],
                'section_filter_index'
            );

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
