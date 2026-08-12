<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Question;

class QuestionPolicy
{
    /**
     * مشاهده لیست
     */
    public function viewAny(User $user): bool
    {
        return $user->can('questions.view');
    }

    /**
     * مشاهده
     */
    public function view(
        User $user,
        Question $question
    ): bool
    {
        return $user->can('questions.view');
    }

    /**
     * ایجاد
     */
    public function create(User $user): bool
    {
        return $user->can('questions.create');
    }

    /**
     * بروزرسانی
     */
    public function update(
        User $user,
        Question $question
    ): bool
    {
        return $user->can('questions.update');
    }

    /**
     * حذف
     */
    public function delete(
        User $user,
        Question $question
    ): bool
    {
        return $user->can('questions.delete');
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        Question $question
    ): bool
    {
        return $user->can('questions.update');
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        Question $question
    ): bool
    {
        return $user->can('questions.delete');
    }
}
