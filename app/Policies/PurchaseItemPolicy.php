<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PurchaseItem;

class PurchaseItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchase-items.view');
    }

    public function view(
        User $user,
        PurchaseItem $purchaseItem
    ): bool
    {
        return $user->can('purchase-items.view');
    }

    public function create(User $user): bool
    {
        return $user->can('purchase-items.create');
    }

    public function update(
        User $user,
        PurchaseItem $purchaseItem
    ): bool
    {
        return $user->can('purchase-items.update');
    }

    public function delete(
        User $user,
        PurchaseItem $purchaseItem
    ): bool
    {
        return $user->can('purchase-items.delete');
    }

    public function restore(
        User $user,
        PurchaseItem $purchaseItem
    ): bool
    {
        return $user->can('purchase-items.update');
    }

    public function forceDelete(
        User $user,
        PurchaseItem $purchaseItem
    ): bool
    {
        return $user->can('purchase-items.delete');
    }
}
