<?php

namespace App\Repositories\Interfaces;

use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseItemRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * آیتم‌های یک خرید
     */
    public function getByPurchase(
        int $purchaseId
    ): Collection;

    /**
     * آیتم‌های یک محصول
     */
    public function getByItem(
        string $itemType,
        int $itemId
    ): Collection;

    /**
     * ایجاد آیتم خرید
     */
    public function createItem(
        array $data
    ): PurchaseItem;
}
