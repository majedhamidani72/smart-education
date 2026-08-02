<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_subject', function (Blueprint $table) {
            $table->id(); // شناسه ترکیب پایه و درس

            $table->foreignId('grade_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // پایه

            $table->foreignId('subject_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // درس

            $table->boolean('is_active')->default(true); // فعال یا غیرفعال

            $table->unsignedSmallInteger('sort_order')->default(1); // ترتیب نمایش

            $table->timestamps();

            $table->unique(
                ['grade_id', 'subject_id'],
                'grade_subject_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_subject');
    }
};
