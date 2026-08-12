<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * مشاهده لیست کتاب‌ها
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('books.view');
    }

    /**
     * مشاهده یک کتاب
     */
    public function view(
        User $user,
        Book $book
    ): bool
    {
        return $user->can('books.view');
    }

    /**
     * ایجاد کتاب
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('books.create');
    }

    /**
     * ویرایش کتاب
     */
    public function update(
        User $user,
        Book $book
    ): bool
    {
        return $user->can('books.update');
    }

    /**
     * حذف کتاب
     */
    public function delete(
        User $user,
        Book $book
    ): bool
    {
        return $user->can('books.delete');
    }

    /**
     * بازیابی کتاب
     */
    public function restore(
        User $user,
        Book $book
    ): bool
    {
        return $user->can('books.update');
    }

    /**
     * حذف دائمی کتاب
     */
    public function forceDelete(
        User $user,
        Book $book
    ): bool
    {
        return $user->can('books.delete');
    }
}
