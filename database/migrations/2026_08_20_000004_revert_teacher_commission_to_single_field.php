<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| بازگشت به یک درصد واحد سهم معلم
|--------------------------------------------------------------------------
| طبق تصمیم جدید، کارمزد هر درگاه پرداخت (که واقعاً یک واقعیت
| اقتصادی سراسری است، نه چیزی که هر معلم/کتاب جدا داشته باشد) از
| مبلغ کل کسر می‌شود؛ سهم معلم روی همان مبلغِ باقی‌مانده (نه مبلغ
| کل) با یک درصد واحد محاسبه می‌شود. دیگر نیازی به سه ستون جدا
| نیست.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {

            $table->unsignedTinyInteger('commission_percentage')
                ->default(60)
                ->after('book_id');

        });

        // مقدار قبلی زیبال (که معمولاً محافظه‌کارانه‌ترین بود) به‌عنوان
        // مقدار شروع درصد واحد جدید استفاده می‌شود.
        DB::statement('
            UPDATE teacher_assignments
            SET commission_percentage = commission_percentage_zibal
        ');

        Schema::table('teacher_assignments', function (Blueprint $table) {

            $table->dropColumn([
                'commission_percentage_zibal',
                'commission_percentage_bazaar',
                'commission_percentage_myket',
            ]);

        });
    }

    public function down(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {

            $table->unsignedTinyInteger('commission_percentage_zibal')->default(30);
            $table->unsignedTinyInteger('commission_percentage_bazaar')->default(30);
            $table->unsignedTinyInteger('commission_percentage_myket')->default(30);

        });

        DB::statement('
            UPDATE teacher_assignments
            SET commission_percentage_zibal = commission_percentage,
                commission_percentage_bazaar = commission_percentage,
                commission_percentage_myket = commission_percentage
        ');

        Schema::table('teacher_assignments', function (Blueprint $table) {

            $table->dropColumn('commission_percentage');

        });
    }
};
