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
     */
    public function view(
        User $user,
        Purchase $purchase
    ): bool
    {
        return $user->can('purchases.view');
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
