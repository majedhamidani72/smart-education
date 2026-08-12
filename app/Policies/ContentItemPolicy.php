<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ContentItem;

class ContentItemPolicy
{
    /**
     * مشاهده لیست محتواها
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('content-items.view');
    }

    /**
     * مشاهده یک محتوا
     */
    public function view(
        User $user,
        ContentItem $contentItem
    ): bool
    {
        return $user->can('content-items.view');
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
