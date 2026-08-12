<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table) {

            $table->id(); // شناسه محتوا

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            $table->foreignId('section_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('content_type_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Content
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            $table->string('slug');

            $table->text('description')->nullable();

            $table->unsignedSmallInteger('page_number')->nullable();

            $table->string('thumbnail')->nullable();

            $table->boolean('is_free')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->string('status', 30)->default('draft');

            $table->text('rejection_reason')->nullable();

            

            // زمان انتشار
            $table->timestamp('published_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Sort
            |--------------------------------------------------------------------------
            */

            $table->unsignedSmallInteger('sort_order')->default(1);

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['section_id', 'slug'],
                'content_section_slug_unique'
            );

            $table->index(
                ['section_id', 'content_type_id', 'status'],
                'content_filter_index'
            );

            $table->index(
                ['is_free', 'status'],
                'content_access_index'
            );

            $table->index(
                ['section_id', 'sort_order'],
                'content_section_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};
