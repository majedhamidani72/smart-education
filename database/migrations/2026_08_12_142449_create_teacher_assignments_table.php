<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {

        Schema::create('teacher_assignments', function (Blueprint $table) {


            $table->id();



            /*
            |--------------------------------------------------------------------------
            | Teacher
            |--------------------------------------------------------------------------
            |
            | معلم دریافت کننده دسترسی
            |
            */


            $table->foreignId('teacher_id')

                ->constrained('users')

                ->cascadeOnUpdate()

                ->restrictOnDelete();





            /*
            |--------------------------------------------------------------------------
            | Book
            |--------------------------------------------------------------------------
            |
            | کتابی که معلم اجازه مدیریت آن را دارد
            |
            */


            $table->foreignId('book_id')

                ->constrained('books')

                ->cascadeOnUpdate()

                ->restrictOnDelete();





            /*
            |--------------------------------------------------------------------------
            | Assigned By
            |--------------------------------------------------------------------------
            |
            | ادمین یا سوپرادمینی که دسترسی را ثبت کرده
            |
            */


            $table->foreignId('assigned_by')

                ->constrained('users')

                ->cascadeOnUpdate()

                ->restrictOnDelete();





            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */


            $table->boolean('is_active')

                ->default(true);





            $table->timestamps();


            $table->softDeletes();





            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */


            $table->index('teacher_id');


            $table->index('book_id');


            $table->index('assigned_by');



            // برای گزارش‌گیری و فیلترهای سریع

            $table->index([

                'teacher_id',

                'is_active'

            ]);



            $table->index([

                'book_id',

                'is_active'

            ]);





            /*
            |--------------------------------------------------------------------------
            | جلوگیری از تخصیص تکراری
            |--------------------------------------------------------------------------
            */


            $table->unique(

                [

                    'teacher_id',

                    'book_id'

                ],

                'teacher_book_unique'

            );


        });

    }





    public function down(): void
    {

        Schema::dropIfExists('teacher_assignments');

    }

};
