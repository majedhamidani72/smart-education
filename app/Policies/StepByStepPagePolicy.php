<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StepByStepPage;

class StepByStepPagePolicy
{
    /**
     * مشاهده لیست صفحات
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('step-by-step-pages.view');
    }

    /**
     * مشاهده صفحه
     */
    public function view(
        User $user,
        StepByStepPage $stepByStepPage
    ): bool
    {
        return $user->can('step-by-step-pages.view');
    }

    /**
     * ایجاد صفحه
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('step-by-step-pages.create');
    }

    /**
     * بروزرسانی صفحه
     */
    public function update(
        User $user,
        StepByStepPage $stepByStepPage
    ): bool
    {
        return $user->can('step-by-step-pages.update');
    }

    /**
     * حذف صفحه
     */
    public function delete(
        User $user,
        StepByStepPage $stepByStepPage
    ): bool
    {
        return $user->can('step-by-step-pages.delete');
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        StepByStepPage $stepByStepPage
    ): bool
    {
        return $user->can('step-by-step-pages.update');
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        StepByStepPage $stepByStepPage
    ): bool
    {
        return $user->can('step-by-step-pages.delete');
    }
}
