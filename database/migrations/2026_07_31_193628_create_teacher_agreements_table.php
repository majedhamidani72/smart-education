<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_agreements', function (Blueprint $table) {

            $table->id();
            // شناسه


            $table->foreignId('teacher_id')
                ->constrained('users')
                ->cascadeOnDelete();
            // معلمی که قوانین را قبول کرده


            $table->string('agreement_version')
                ->default('1.0');
            // نسخه قوانین
            // اگر قوانین تغییر کرد نسخه جدید ثبت می‌کنیم


            $table->timestamp('accepted_at');
            // زمان تایید


            $table->string('ip_address')
                ->nullable();
            // آی‌پی زمان تایید


            $table->text('user_agent')
                ->nullable();
            // اطلاعات مرورگر یا دستگاه


            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('teacher_agreements');
    }
};
