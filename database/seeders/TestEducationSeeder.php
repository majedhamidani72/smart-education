<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Subject;


class TestEducationSeeder extends Seeder
{
    public function run(): void
    {

        // ساخت پایه پنجم
        $grade = Grade::create([

            'title' => 'پنجم',

            'slug' => 'fifth-grade',

            'grade_number' => 5,

            'sort_order' => 1,

            'is_active' => true,

        ]);



        // ساخت درس ریاضی
        $subject = Subject::create([

            'title' => 'ریاضی',

            'slug' => 'mathematics',

            'description' => 'درس ریاضی',

            'sort_order' => 1,

            'is_active' => true,

        ]);



        // اتصال پایه به درس
        $grade->subjects()->attach($subject->id);

    }
}
