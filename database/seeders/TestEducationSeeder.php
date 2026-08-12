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
        */

        if (! $grade->subjects()->where('subject_id', $subject->id)->exists()) {

            $grade->subjects()->attach($subject->id);

        }
    }
}
