<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| افزودن «پایه» به لیست نوع آیتم خرید
|--------------------------------------------------------------------------
| طبق تصمیم پروژه، برای پایه‌های ۱ تا ۶ یک خرید کل پایه را باز
| می‌کند (نه فقط یک کتاب). enum قبلی این حالت را نداشت.
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE purchase_items
            MODIFY COLUMN item_type ENUM('book', 'lesson', 'subscription', 'package', 'quiz', 'grade')
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE purchase_items
            MODIFY COLUMN item_type ENUM('book', 'lesson', 'subscription', 'package', 'quiz')
        ");
    }
};
