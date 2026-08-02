<?php

namespace App\Policies;

use App\Models\ContentItem;
use App\Models\User;

class ContentItemPolicy
{

    // مشاهده لیست محتوا
    public function viewAny(User $user): bool
    {
        return true;
    }



    // مشاهده یک محتوا
    public function view(
        User $user,
        ContentItem $contentItem
    ): bool {

        // ادمین همه چیز را می‌بیند
        if ($user->hasRole('Admin')) {
            return true;
        }


        // معلم فقط محتوای خودش
        return $contentItem->created_by === $user->id;
    }



    // ساخت محتوا
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Admin',
            'Teacher'
        ]);
    }



    // ویرایش محتوا
    public function update(
        User $user,
        ContentItem $contentItem
    ): bool {


        if ($user->hasRole('Admin')) {
            return true;
        }


        return $contentItem->created_by === $user->id;
    }



    // حذف محتوا
    public function delete(
        User $user,
        ContentItem $contentItem
    ): bool {


        // فقط ادمین
        return $user->hasRole('Admin');

    }

}
