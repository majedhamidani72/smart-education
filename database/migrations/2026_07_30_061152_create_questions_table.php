<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */


            // سازنده سوال
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            // مدیر بررسی کننده
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            // محتوای آموزشی مربوط به سوال
            $table->foreignId('content_item_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();


            // موضوع سوال برای تحلیل هوشمند
            $table->foreignId('question_topic_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();



            /*
            |--------------------------------------------------------------------------
            | Question Content
            |--------------------------------------------------------------------------
            */


            // متن سوال
            $table->text('question_text')
                ->nullable();


            // تصویر سوال
            $table->string('image_path')
                ->nullable();


            // توضیح پاسخ صحیح
            $table->text('explanation')
                ->nullable();


            // تصویر توضیح پاسخ
            $table->string('explanation_image_path')
                ->nullable();



            /*
            |--------------------------------------------------------------------------
            | Question Settings
            |--------------------------------------------------------------------------
            */


            // easy / medium / hard
            $table->string('difficulty', 20)
                ->default('easy');


            // امتیاز سوال
            $table->unsignedSmallInteger('default_score')
                ->default(1);


            // draft / pending / approved / rejected
            $table->string('status', 30)
                ->default('draft');


            // دلیل رد شدن
            $table->text('rejection_reason')
                ->nullable();


            // فعال یا غیرفعال
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


            $table->index('content_item_id');

            $table->index('question_topic_id');

            $table->index('created_by');

            $table->index('reviewed_by');


            $table->index(
                [
                    'difficulty',
                    'status',
                    'is_active'
                ],
                'question_filter_index'
            );

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
