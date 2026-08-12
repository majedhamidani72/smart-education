<?php

namespace App\Policies;

use App\Models\User;
use App\Models\QuestionOption;

class QuestionOptionPolicy
{
    /**
     * مشاهده لیست
     */
    public function viewAny(User $user): bool
    {
        return $user->can('question-options.view');
    }

    /**
     * مشاهده
     */
    public function view(
        User $user,
        QuestionOption $questionOption
    ): bool
    {
        return $user->can('question-options.view');
    }

    /**
     * ایجاد
     */
    public function create(User $user): bool
    {
        return $user->can('question-options.create');
    }

    /**
     * بروزرسانی
     */
    public function update(
        User $user,
        QuestionOption $questionOption
    ): bool
    {
        return $user->can('question-options.update');
    }

    /**
     * حذف
     */
    public function delete(
        User $user,
        QuestionOption $questionOption
    ): bool
    {
        return $user->can('question-options.delete');
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        QuestionOption $questionOption
    ): bool
    {
        return $user->can('question-options.update');
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        QuestionOption $questionOption
    ): bool
    {
        return $user->can('question-options.delete');
    }
}
