<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id(); // شناسه گزینه

            $table->foreignId('question_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // سؤال

            $table->text('option_text')->nullable(); // متن گزینه

            $table->string('image_path')->nullable(); // تصویر گزینه

            $table->boolean('is_correct')->default(false); // گزینه صحیح

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب گزینه

            $table->timestamps();

            $table->index(
                ['question_id', 'is_correct'],
                'question_option_correct_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
