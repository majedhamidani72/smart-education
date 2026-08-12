<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quiz;

class QuizPolicy
{
    /**
     * مشاهده لیست
     */
    public function viewAny(User $user): bool
    {
        return $user->can('quizzes.view');
    }

    /**
     * مشاهده
     */
    public function view(
        User $user,
        Quiz $quiz
    ): bool
    {
        return $user->can('quizzes.view');
    }

    /**
     * ایجاد
     */
    public function create(User $user): bool
    {
        return $user->can('quizzes.create');
    }

    /**
     * بروزرسانی
     */
    public function update(
        User $user,
        Quiz $quiz
    ): bool
    {
        return $user->can('quizzes.update');
    }

    /**
     * حذف
     */
    public function delete(
        User $user,
        Quiz $quiz
    ): bool
    {
        return $user->can('quizzes.delete');
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        Quiz $quiz
    ): bool
    {
        return $user->can('quizzes.update');
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        Quiz $quiz
    ): bool
    {
        return $user->can('quizzes.delete');
    }
}
