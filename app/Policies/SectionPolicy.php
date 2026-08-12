<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    /**
     * مشاهده لیست بخش‌ها
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('sections.view');
    }

    /**
     * مشاهده یک بخش
     */
    public function view(
        User $user,
        Section $section
    ): bool
    {
        return $user->can('sections.view');
    }

    /**
     * ایجاد بخش
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('sections.create');
    }

    /**
     * ویرایش بخش
     */
    public function update(
        User $user,
        Section $section
    ): bool
    {
        return $user->can('sections.update');
    }

    /**
     * حذف بخش
     */
    public function delete(
        User $user,
        Section $section
    ): bool
    {
        return $user->can('sections.delete');
    }

    /**
     * بازیابی بخش
     */
    public function restore(
        User $user,
        Section $section
    ): bool
    {
        return $user->can('sections.update');
    }

    /**
     * حذف دائمی بخش
     */
    public function forceDelete(
        User $user,
        Section $section
    ): bool
    {
        return $user->can('sections.delete');
    }
}
