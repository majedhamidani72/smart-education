<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| نیم‌سال هر فصل/درس
|--------------------------------------------------------------------------
| فقط برای درس‌هایی که ساختار آزمونشان «درس و نیم‌سال» است معنا
| دارد: مشخص می‌کند این فصل/درس جزو نیم‌سال اول کتاب است یا دوم،
| تا آزمون «نوبت اول» بتواند فقط از نیم اول کتاب سوال بکشد (طبق
| آیین‌نامه‌ی رسمی وزارت آموزش و پرورش).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {

            $table->unsignedTinyInteger('term')
                ->nullable()
                ->after('sort_order');

        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {

            $table->dropColumn('term');

        });
    }
};
