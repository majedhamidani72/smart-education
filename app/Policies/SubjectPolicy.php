<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    /**
     * مشاهده لیست درس‌ها
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('subjects.view');
    }

    /**
     * مشاهده یک درس
     */
    public function view(
        User $user,
        Subject $subject
    ): bool
    {
        return $user->can('subjects.view');
    }

    /**
     * ایجاد درس
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('subjects.create');
    }

    /**
     * ویرایش درس
     */
    public function update(
        User $user,
        Subject $subject
    ): bool
    {
        return $user->can('subjects.update');
    }

    /**
     * حذف درس
     */
    public function delete(
        User $user,
        Subject $subject
    ): bool
    {
        return $user->can('subjects.delete');
    }

    /**
     * بازیابی درس
     */
    public function restore(
        User $user,
        Subject $subject
    ): bool
    {
        return $user->can('subjects.update');
    }

    /**
     * حذف دائمی درس
     */
    public function forceDelete(
        User $user,
        Subject $subject
    ): bool
    {
        return $user->can('subjects.delete');
    }
}
