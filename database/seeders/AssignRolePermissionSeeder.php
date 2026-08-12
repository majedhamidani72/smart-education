<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignRolePermissionSeeder extends Seeder
{
    /**
     * اجرای Seeder
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | دریافت Role ها
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::findByName('SuperAdmin');

        $admin = Role::findByName('Admin');

        $teacher = Role::findByName('Teacher');

        $student = Role::findByName('Student');

        $parent = Role::findByName('Parent');

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions(

            Permission::all()

        );

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $admin->syncPermissions([

            'grades.view',
            'grades.create',
            'grades.update',
            'grades.delete',

            'subjects.view',
            'subjects.create',
            'subjects.update',
            'subjects.delete',

            'books.view',
            'books.create',
            'books.update',
            'books.delete',

            'chapters.view',
            'chapters.create',
            'chapters.update',
            'chapters.delete',

            'sections.view',
            'sections.create',
            'sections.update',
            'sections.delete',

            'content-items.view',
            'content-items.create',
            'content-items.update',
            'content-items.delete',
            'content-items.publish',
            'content-items.approve',
            'content-items.reject',

            'videos.view',
            'videos.create',
            'videos.update',
            'videos.delete',
            'videos.approve',
            'videos.reject',

            'pdf-files.view',
            'pdf-files.create',
            'pdf-files.update',
            'pdf-files.delete',

            'step-by-step-pages.view',
            'step-by-step-pages.create',
            'step-by-step-pages.update',
            'step-by-step-pages.delete',

            'sample-questions.view',
            'sample-questions.create',
            'sample-questions.update',
            'sample-questions.delete',

            'quizzes.view',
            'quizzes.create',
            'quizzes.update',
            'quizzes.delete',

            'questions.view',
            'questions.create',
            'questions.update',
            'questions.delete',

            'question-options.view',
            'question-options.create',
            'question-options.update',
            'question-options.delete',

            'plans.view',
            'plans.create',
            'plans.update',
            'plans.delete',

            'subscriptions.view',

            'payment-transactions.view',

            'users.view',
            'users.update',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Teacher
        |--------------------------------------------------------------------------
        */

        $teacher->syncPermissions([

            'videos.view',
            'videos.create',
            'videos.update',

            'pdf-files.view',
            'pdf-files.create',
            'pdf-files.update',

            'step-by-step-pages.view',
            'step-by-step-pages.create',
            'step-by-step-pages.update',

            'sample-questions.view',
            'sample-questions.create',
            'sample-questions.update',

            'quizzes.view',
            'quizzes.create',
            'quizzes.update',

            'questions.view',
            'questions.create',
            'questions.update',

            'question-options.view',
            'question-options.create',
            'question-options.update',

            'content-items.view',
            'content-items.create',
            'content-items.update',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        $student->syncPermissions([

            'grades.view',

            'subjects.view',

            'books.view',

            'chapters.view',

            'sections.view',

            'videos.view',

            'pdf-files.view',

            'step-by-step-pages.view',

            'sample-questions.view',

            'quizzes.view',

            'questions.view',

            'plans.view',

            'purchases.create',

            'subscriptions.view',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Parent
        |--------------------------------------------------------------------------
        */

        $parent->syncPermissions([

            'grades.view',

            'subjects.view',

            'books.view',

            'chapters.view',

            'sections.view',

            'subscriptions.view',

        ]);
    }
}
