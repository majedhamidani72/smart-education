<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PdfFile;

class PdfFilePolicy
{
    /**
     * مشاهده لیست فایل‌ها
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('pdf-files.view');
    }

    /**
     * مشاهده فایل
     */
    public function view(
        User $user,
        PdfFile $pdfFile
    ): bool
    {
        return $user->can('pdf-files.view');
    }

    /**
     * ایجاد فایل
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('pdf-files.create');
    }

    /**
     * بروزرسانی فایل
     */
    public function update(
        User $user,
        PdfFile $pdfFile
    ): bool
    {
        return $user->can('pdf-files.update');
    }

    /**
     * حذف فایل
     */
    public function delete(
        User $user,
        PdfFile $pdfFile
    ): bool
    {
        return $user->can('pdf-files.delete');
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        PdfFile $pdfFile
    ): bool
    {
        return $user->can('pdf-files.update');
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        PdfFile $pdfFile
    ): bool
    {
        return $user->can('pdf-files.delete');
    }
}
