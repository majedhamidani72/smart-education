<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_question', function (Blueprint $table) {
            $table->id(); // شناسه

            $table->foreignId('quiz_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // آزمون

            $table->foreignId('question_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // سؤال

            $table->unsignedTinyInteger('score')
                ->default(1);

            $table->unsignedSmallInteger('sort_order')
                ->default(1);

            $table->index([
                'quiz_id',
                'sort_order'
            ]);

            $table->timestamps();

            $table->unique(
                ['quiz_id', 'question_id'],
                'quiz_question_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_question');
    }
};
