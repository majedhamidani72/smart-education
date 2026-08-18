<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| محدوده‌ی نیم‌سال آزمون (فقط برای درس‌های «درس و نیم‌سال»)
|--------------------------------------------------------------------------
| وقتی آزمون از نوع «نوبت اول» باشد، این ستون ۱ می‌شود و فقط از
| فصل‌های نیم‌سال اول کتاب سوال کشیده می‌شود. برای «نوبت دوم»
| (کل کتاب) یا آزمون‌های معمولی، مقدارش خالی می‌ماند.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {

            $table->unsignedTinyInteger('term_scope')
                ->nullable()
                ->after('quizable_id');

        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {

            $table->dropColumn('term_scope');

        });
    }
};
