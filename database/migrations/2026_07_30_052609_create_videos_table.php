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
            | ارتباط با محتوای آموزشی
            |--------------------------------------------------------------------------
            |
            | هر ویدئو متعلق به یک ContentItem است.
            | ContentItem به Section، Chapter و Book متصل است.
            |
            */

            $table->foreignId('content_item_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();



            /*
            |--------------------------------------------------------------------------
            | آپلود کننده
            |--------------------------------------------------------------------------
            */

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();



            /*
            |--------------------------------------------------------------------------
            | تایید کننده
            |--------------------------------------------------------------------------
            */

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | اطلاعات فایل
            |--------------------------------------------------------------------------
            */

            $table->string('directory');


            $table->string('filename')
                ->index();


            $table->string('original_name');


            $table->string('extension', 20);


            $table->string('mime_type', 100);


            $table->unsignedBigInteger('file_size');



            /*
            |--------------------------------------------------------------------------
            | اطلاعات ویدئو
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('duration')
                ->nullable();


            $table->string('quality', 30)
                ->nullable();


            $table->string('thumbnail_path')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | آمار و دسترسی
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('views_count')
                ->default(0);


            $table->boolean('download_allowed')
                ->default(false);



            /*
            |--------------------------------------------------------------------------
            | گردش تایید محتوا
            |--------------------------------------------------------------------------
            */

            $table->string('processing_status')
                ->default('pending');


            $table->timestamp('approved_at')
                ->nullable();


            $table->text('rejected_reason')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | زمان‌ها
            |--------------------------------------------------------------------------
            */

            $table->timestamps();


            $table->softDeletes();



            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('content_item_id');

            $table->index('uploaded_by');

            $table->index('approved_by');

            $table->index('processing_status');

            $table->index('views_count');

        });
    }



    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
