<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | چرا این ستون لازم است
    |--------------------------------------------------------------------------
    | تا پیش از این، فقط «نسخه»‌ی قرارداد (هش ۱۲ کاراکتری از متن) ذخیره
    | می‌شد، نه خودِ متن. اگر مدیر بعداً متن قرارداد را در تنظیمات
    | ویرایش کند، دیگر نمی‌شود دقیقاً همان متنی که فلان معلم/ادمین در
    | فلان تاریخ پذیرفته را بازسازی و چاپ کرد — فقط متن فعلی در دسترس
    | می‌ماند. با ذخیره‌ی یک نسخه (Snapshot) از متن در لحظه‌ی پذیرش،
    | خروجی چاپ همیشه دقیقاً همان چیزی است که واقعاً امضا/تایید شده.
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::table('teacher_agreements', function (Blueprint $table) {

            $table->longText('agreement_text')
                ->nullable()
                ->after('agreement_version');

        });
    }

    public function down(): void
    {
        Schema::table('teacher_agreements', function (Blueprint $table) {

            $table->dropColumn('agreement_text');

        });
    }
};
