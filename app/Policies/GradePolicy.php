<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    /**
     * مشاهده لیست پایه‌ها
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('grades.view');
    }

    /**
     * مشاهده یک پایه
     */
    public function view(
        User $user,
        Grade $grade
    ): bool
    {
        return $user->can('grades.view');
    }

    /**
     * ایجاد پایه
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('grades.create');
    }

    /**
     * ویرایش پایه
     */
    public function update(
        User $user,
        Grade $grade
    ): bool
    {
        return $user->can('grades.update');
    }

    /**
     * حذف پایه
     */
    public function delete(
        User $user,
        Grade $grade
    ): bool
    {
        return $user->can('grades.delete');
    }

    /**
     * بازیابی پایه
     */
    public function restore(
        User $user,
        Grade $grade
    ): bool
    {
        return $user->can('grades.update');
    }

    /**
     * حذف دائمی پایه
     */
    public function forceDelete(
        User $user,
        Grade $grade
    ): bool
    {
        return $user->can('grades.delete');
    }
}
