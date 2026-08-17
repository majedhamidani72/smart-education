<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('app_grade_subject_id')
                ->constrained('app_grade_subjects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Book Information
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->string('slug');

            $table->string('cover')->nullable();

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
                    'app_grade_subject_id',
                    'slug',
                ],
                'book_app_grade_subject_slug_unique'
            );

            $table->index(
                [
                    'app_grade_subject_id',
                    'sort_order',
                ],
                'book_sort_index'
            );

            $table->index(
                [
                    'app_grade_subject_id',
                    'is_active',
                ],
                'book_filter_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
