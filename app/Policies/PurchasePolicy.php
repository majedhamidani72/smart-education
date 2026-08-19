<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Purchase;

class PurchasePolicy
{
    /**
     * مشاهده لیست
     */
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.view');
    }

    /**
     * مشاهده
     * --------------------------------------------------------------------
     * دو حالت مجاز است: یا کاربر مجوز عمومی «مشاهده‌ی خریدها» را
     * دارد (مثل ادمین در پنل مدیریتی)، یا این خرید متعلق به خودِ
     * همین کاربر است (مثل دانش‌آموزی که می‌خواهد خرید خودش را
     * پرداخت کند — نقش دانش‌آموز اصلاً مجوز purchases.view ندارد
     * و نباید هم داشته باشد، چون قرار نیست خریدهای بقیه را ببیند).
     */
    public function view(
        User $user,
        Purchase $purchase
    ): bool
    {
        return $user->can('purchases.view')
            || (int) $purchase->user_id === (int) $user->id;
    }

    /**
     * ایجاد
     */
    public function create(User $user): bool
    {
        return $user->can('purchases.create');
    }

    /**
     * بروزرسانی
     */
    public function update(
        User $user,
        Purchase $purchase
    ): bool
    {
        return $user->can('purchases.update');
    }

    /**
     * حذف
     */
    public function delete(
        User $user,
        Purchase $purchase
    ): bool
    {
        return $user->can('purchases.delete');
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        Purchase $purchase
    ): bool
    {
        return $user->can('purchases.update');
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        Purchase $purchase
    ): bool
    {
        return $user->can('purchases.delete');
    }
}
