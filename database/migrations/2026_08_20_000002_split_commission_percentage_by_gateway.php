<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| درصد سهم معلم جداگانه به‌ازای هر درگاه فروش
|--------------------------------------------------------------------------
| چون بازار و مایکت خودشان کارمزد بالایی (حدود ۳۰٪) از فروش
| کم می‌کنند، درصد سهم معلم می‌تواند بسته به این‌که خرید از کجا
| آمده (سایت/اپ مستقیم با زیبال، یا از بازار، یا از مایکت) فرق
| داشته باشد.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {

            $table->unsignedTinyInteger('commission_percentage_zibal')
                ->default(30)
                ->after('commission_percentage');

            $table->unsignedTinyInteger('commission_percentage_bazaar')
                ->default(30)
                ->after('commission_percentage_zibal');

            $table->unsignedTinyInteger('commission_percentage_myket')
                ->default(30)
                ->after('commission_percentage_bazaar');

        });

        // مقدار قبلی (تکی) به‌عنوان مقدار شروع هر سه ستون کپی
        // می‌شود، تا دیتای موجود از دست نرود.
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

    public function down(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {

            $table->unsignedTinyInteger('commission_percentage')->default(30);

        });

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
};
