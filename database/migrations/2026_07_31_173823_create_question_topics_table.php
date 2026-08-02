<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_topics', function (Blueprint $table) {

        $table->id(); // شناسه موضوع

        $table->string('title'); // نام موضوع مثل کسرها، ضرب، تقسیم

        $table->text('description')
            ->nullable(); // توضیحات موضوع

        $table->timestamps();

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_topics');
    }
};
