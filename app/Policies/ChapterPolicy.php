<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\User;

class ChapterPolicy
{
    /**
     * مشاهده لیست فصل‌ها
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('chapters.view');
    }

    /**
     * مشاهده یک فصل
     */
    public function view(
        User $user,
        Chapter $chapter
    ): bool
    {
        return $user->can('chapters.view');
    }

    /**
     * ایجاد فصل
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('chapters.create');
    }

    /**
     * ویرایش فصل
     */
    public function update(
        User $user,
        Chapter $chapter
    ): bool
    {
        return $user->can('chapters.update');
    }

    /**
     * حذف فصل
     */
    public function delete(
        User $user,
        Chapter $chapter
    ): bool
    {
        return $user->can('chapters.delete');
    }

    /**
     * بازیابی فصل
     */
    public function restore(
        User $user,
        Chapter $chapter
    ): bool
    {
        return $user->can('chapters.update');
    }

    /**
     * حذف دائمی فصل
     */
    public function forceDelete(
        User $user,
        Chapter $chapter
    ): bool
    {
        return $user->can('chapters.delete');
    }
}
