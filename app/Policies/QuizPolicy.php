<?php

namespace App\Policies;


use App\Models\User;
use App\Models\Quiz;


class QuizPolicy
{


    /**
     * مشاهده لیست آزمون‌ها
     */
    public function viewAny(User $user): bool
    {

        return $user->can('quizzes.view');

    }




    /**
     * مشاهده یک آزمون
     */
    public function view(
        User $user,
        Quiz $quiz
    ): bool
    {

        if (
            $user->hasRole('SuperAdmin')
            ||
            $user->hasRole('Admin')
        ) {

            return true;

        }



        return $this->hasAccessToQuiz(
            $user,
            $quiz
        );

    }




    /**
     * ایجاد آزمون
     */
    public function create(User $user): bool
    {

        return $user->can(
            'quizzes.create'
        );

    }




    /**
     * ویرایش آزمون
     */
    public function update(
        User $user,
        Quiz $quiz
    ): bool
    {


        if (
            $user->hasRole('SuperAdmin')
            ||
            $user->hasRole('Admin')
        ) {

            return true;

        }



        return

            $user->can('quizzes.update')

            &&

            $this->hasAccessToQuiz(
                $user,
                $quiz
            );


    }




    /**
     * حذف آزمون
     */
    public function delete(
        User $user,
        Quiz $quiz
    ): bool
    {


        if (
            $user->hasRole('SuperAdmin')
        ) {

            return true;

        }



        return

            $user->can('quizzes.delete')

            &&

            $this->hasAccessToQuiz(
                $user,
                $quiz
            );


    }




    /**
     * بازیابی
     */
    public function restore(
        User $user,
        Quiz $quiz
    ): bool
    {

        return $this->delete(
            $user,
            $quiz
        );

    }




    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        Quiz $quiz
    ): bool
    {

        return $user->hasRole(
            'SuperAdmin'
        );

    }





    /**
     * بررسی دسترسی معلم به کتاب آزمون
     */
    protected function hasAccessToQuiz(
        User $user,
        Quiz $quiz
    ): bool
    {


        /*
        |--------------------------------------------------------------------------
        | اگر آزمون مربوط به کتاب باشد
        |--------------------------------------------------------------------------
        */


        if (
            $quiz->quizable_type === \App\Models\Book::class
        ) {


            return $quiz
                ->quizable
                ->teacherAssignments()
                ->where(
                    'teacher_id',
                    $user->id
                )
                ->where(
                    'is_active',
                    true
                )
                ->exists();


        }





        /*
        |--------------------------------------------------------------------------
        | اگر آزمون مربوط به فصل باشد
        |--------------------------------------------------------------------------
        */


        if (
            $quiz->quizable_type === \App\Models\Chapter::class
        ) {


            return $quiz
                ->quizable
                ->book
                ->teacherAssignments()
                ->where(
                    'teacher_id',
                    $user->id
                )
                ->where(
                    'is_active',
                    true
                )
                ->exists();


        }





        /*
        |--------------------------------------------------------------------------
        | اگر آزمون مربوط به بخش باشد
        |--------------------------------------------------------------------------
        */


        if (
            $quiz->quizable_type === \App\Models\Section::class
        ) {


            return $quiz
                ->quizable
                ->chapter
                ->book
                ->teacherAssignments()
                ->where(
                    'teacher_id',
                    $user->id
                )
                ->where(
                    'is_active',
                    true
                )
                ->exists();


        }



        return false;


    }


}
