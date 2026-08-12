<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SampleQuestion;

class SampleQuestionPolicy
{
    /**
     * مشاهده لیست
     */
    public function viewAny(User $user): bool
    {
        return $user->can('sample-questions.view');
    }

    /**
     * مشاهده
     */
    public function view(
        User $user,
        SampleQuestion $sampleQuestion
    ): bool
    {
        return $user->can('sample-questions.view');
    }

    /**
     * ایجاد
     */
    public function create(User $user): bool
    {
        return $user->can('sample-questions.create');
    }

    /**
     * بروزرسانی
     */
    public function update(
        User $user,
        SampleQuestion $sampleQuestion
    ): bool
    {
        return $user->can('sample-questions.update');
    }

    /**
     * حذف
     */
    public function delete(
        User $user,
        SampleQuestion $sampleQuestion
    ): bool
    {
        return $user->can('sample-questions.delete');
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        SampleQuestion $sampleQuestion
    ): bool
    {
        return $user->can('sample-questions.update');
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        SampleQuestion $sampleQuestion
    ): bool
    {
        return $user->can('sample-questions.delete');
    }
}
