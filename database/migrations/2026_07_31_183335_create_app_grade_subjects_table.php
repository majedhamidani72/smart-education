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
        Schema::create('app_grade_subjects', function (Blueprint $table) {

        $table->id();

        $table->foreignId('app_id')
            ->constrained()
            ->cascadeOnDelete();
        // اپلیکیشن آموزشی
        // مثال: اپ ریاضی پنجم


        $table->foreignId('grade_id')
            ->constrained()
            ->cascadeOnDelete();
        // پایه تحصیلی
        // مثال: پنجم ابتدایی


        $table->foreignId('subject_id')
            ->constrained()
            ->cascadeOnDelete();
        // درس
        // مثال: ریاضی


        $table->timestamps();


        // جلوگیری از ثبت تکراری
        $table->unique([
            'app_id',
            'grade_id',
            'subject_id'
        ]);

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_grade_subjects');
    }
};
