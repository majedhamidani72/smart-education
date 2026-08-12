<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * اجرای Seeder
     */
    public function run(): void
    {
        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Grades
            |--------------------------------------------------------------------------
            */

            'grades.view',
            'grades.create',
            'grades.update',
            'grades.delete',

            /*
            |--------------------------------------------------------------------------
            | Subjects
            |--------------------------------------------------------------------------
            */

            'subjects.view',
            'subjects.create',
            'subjects.update',
            'subjects.delete',

            /*
            |--------------------------------------------------------------------------
            | Books
            |--------------------------------------------------------------------------
            */

            'books.view',
            'books.create',
            'books.update',
            'books.delete',

            /*
            |--------------------------------------------------------------------------
            | Chapters
            |--------------------------------------------------------------------------
            */

            'chapters.view',
            'chapters.create',
            'chapters.update',
            'chapters.delete',

            /*
            |--------------------------------------------------------------------------
            | Sections
            |--------------------------------------------------------------------------
            */

            'sections.view',
            'sections.create',
            'sections.update',
            'sections.delete',

            /*
            |--------------------------------------------------------------------------
            | Content Items
            |--------------------------------------------------------------------------
            */

            'content-items.view',
            'content-items.create',
            'content-items.update',
            'content-items.delete',
            'content-items.submit',
            'content-items.publish',
            'content-items.unpublish',
            'content-items.approve',
            'content-items.reject',

            /*
            |--------------------------------------------------------------------------
            | Videos
            |--------------------------------------------------------------------------
            */

            'videos.view',

            'videos.create',

            'videos.update',

            'videos.delete',

            'videos.approve',

            'videos.reject',

            /*
            |--------------------------------------------------------------------------
            | PDF Files
            |--------------------------------------------------------------------------
            */

            'pdf-files.view',
            'pdf-files.create',
            'pdf-files.update',
            'pdf-files.delete',

            /*
            |--------------------------------------------------------------------------
            | Step By Step Pages
            |--------------------------------------------------------------------------
            */

            'step-by-step-pages.view',
            'step-by-step-pages.create',
            'step-by-step-pages.update',
            'step-by-step-pages.delete',

            /*
            |--------------------------------------------------------------------------
            | Sample Questions
            |--------------------------------------------------------------------------
            */

            'sample-questions.view',
            'sample-questions.create',
            'sample-questions.update',
            'sample-questions.delete',

            /*
            |--------------------------------------------------------------------------
            | Quizzes
            |--------------------------------------------------------------------------
            */

            'quizzes.view',
            'quizzes.create',
            'quizzes.update',
            'quizzes.delete',

            /*
            |--------------------------------------------------------------------------
            | Questions
            |--------------------------------------------------------------------------
            */

            'questions.view',
            'questions.create',
            'questions.update',
            'questions.delete',

            /*
            |--------------------------------------------------------------------------
            | Question Options
            |--------------------------------------------------------------------------
            */

            'question-options.view',
            'question-options.create',
            'question-options.update',
            'question-options.delete',

            /*
            |--------------------------------------------------------------------------
            | Purchases
            |--------------------------------------------------------------------------
            */

            'purchases.view',
            'purchases.create',
            'purchases.update',
            'purchases.delete',

            /*
            |--------------------------------------------------------------------------
            | Purchase Items
            |--------------------------------------------------------------------------
            */

            'purchase-items.view',
            'purchase-items.create',
            'purchase-items.update',
            'purchase-items.delete',

            /*
            |--------------------------------------------------------------------------
            | Plans
            |--------------------------------------------------------------------------
            */

            'plans.view',
            'plans.create',
            'plans.update',
            'plans.delete',

            /*
            |--------------------------------------------------------------------------
            | Subscriptions
            |--------------------------------------------------------------------------
            */

            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.update',
            'subscriptions.delete',

            /*
            |--------------------------------------------------------------------------
            | Payment Transactions
            |--------------------------------------------------------------------------
            */

            'payment-transactions.view',
            'payment-transactions.create',
            'payment-transactions.update',
            'payment-transactions.delete',

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'users.block',
            'users.unblock',

            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */

            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            /*
            |--------------------------------------------------------------------------
            | Permissions
            |--------------------------------------------------------------------------
            */

            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',


            /*
        |--------------------------------------------------------------------------
        | Devices
        |--------------------------------------------------------------------------
        */

            'devices.view',
            'devices.create',
            'devices.update',
            'devices.delete',


            /*
        |--------------------------------------------------------------------------
        | OTP Codes
        |--------------------------------------------------------------------------
        */

            'otp-codes.view',
            'otp-codes.create',
            'otp-codes.update',
            'otp-codes.delete',

        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([

                'name' => $permission,

                'guard_name' => 'web',

            ]);
        }
    }
}
