<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Subject;

class TestEducationSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | پایه پنجم
        |--------------------------------------------------------------------------
        */

        $grade = Grade::firstOrCreate(

            [

                'slug' => 'fifth-grade',

            ],

            [

                'title' => 'پنجم',

                'grade_number' => 5,

                'sort_order' => 1,

                'is_active' => true,

            ]

        );

        /*
        |--------------------------------------------------------------------------
        | درس ریاضی
        |--------------------------------------------------------------------------
        */

        $subject = Subject::firstOrCreate(

            [

                'slug' => 'mathematics',

            ],

            [

                'title' => 'ریاضی',

                'description' => 'درس ریاضی',

                'sort_order' => 1,

                'is_active' => true,

            ]

        );

        /*
        |--------------------------------------------------------------------------
        | اتصال پایه و درس
        |--------------------------------------------------------------------------
        | این سیدر فقط برای تست اولیه‌ی وجود پایه/درس است — برای
        | داده‌ی نمایشی کامل (با کتاب، معلم، محتوا، آزمون) از
        | DemoContentSeeder استفاده کن که مسیر واقعی و کامل
        | (App → Grade → Subject → AppGradeSubject → Book) را
        | رعایت می‌کند.
        */
    }
}
