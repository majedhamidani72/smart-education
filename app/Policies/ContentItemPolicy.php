<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContentItem;

class ContentItemPolicy
{
    /**
     * مشاهده لیست محتواها
     * --------------------------------------------------------------------
     * برای دانش‌آموز، این چک اجازه‌ی «باز کردن لیست» را می‌دهد؛
     * فیلتر کردن این‌که کدام آیتم‌ها واقعاً قابل‌مشاهده‌اند (رایگان
     * یا خریداری‌شده) در سطح کوئری/سرویس انجام می‌شود، نه اینجا.
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('content-items.view')
            || $user->hasRole('Student');
    }

    /**
     * مشاهده یک محتوا
     * --------------------------------------------------------------------
     * سه حالت مجاز است:
     * ۱) کاربر مجوز مدیریتی عمومی دارد (ادمین/معلم)
     * ۲) محتوا رایگان است — هرکسی می‌تواند ببیند
     * ۳) کاربر (دانش‌آموز) اشتراک فعالی دارد که این کتاب یا پایه‌ی
     *    مربوطه را پوشش می‌دهد
     */
    public function view(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        if ($user->can('content-items.view')) {
            return true;
        }

        if ($contentItem->is_free) {
            return true;
        }

        return $user->hasAccessToContentItem($contentItem);
    }

    /**
     * ایجاد محتوا
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('content-items.create');
    }

    /**
     * ویرایش محتوا
     */
    public function update(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.update');
    }

    /**
     * حذف محتوا
     */
    public function delete(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.delete');
    }

    /**
     * ارسال برای بررسی
     */
    public function submit(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.submit');
    }

    /**
     * تأیید محتوا
     */
    public function approve(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.approve');
    }

    /**
     * رد محتوا
     */
    public function reject(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.reject');
    }

    /**
     * انتشار محتوا
     */
    public function publish(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.publish');
    }

    /**
     * لغو انتشار محتوا
     */
    public function unpublish(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.unpublish');
    }

    /**
     * بازیابی محتوا
     */
    public function restore(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.update');
    }

    /**
     * حذف دائمی محتوا
     */
    public function forceDelete(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.delete');
    }
}
