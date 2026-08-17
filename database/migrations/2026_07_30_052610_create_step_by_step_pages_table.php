<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('step_by_step_pages', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Step By Step
            |--------------------------------------------------------------------------
            */

            $table->foreignId('step_by_step_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Page Information
            |--------------------------------------------------------------------------
            */

            $table->unsignedSmallInteger('page_number');

            $table->string('image');

            $table->unsignedSmallInteger('sort_order')
                ->default(1);

            $table->boolean('is_free')
                ->default(false);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['step_by_step_id', 'page_number'],
                'step_page_unique'
            );

            $table->index(
                ['step_by_step_id', 'sort_order'],
                'step_page_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('step_by_step_pages');
    }
};
