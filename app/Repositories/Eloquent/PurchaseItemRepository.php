<?php

namespace App\Repositories\Eloquent;

use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;

class PurchaseItemRepository extends BaseRepository implements PurchaseItemRepositoryInterface
{
    public function __construct(
        PurchaseItem $model
    ) {
        parent::__construct($model);
    }

    /**
     * آیتم‌های یک خرید
     */
    public function getByPurchase(
        int $purchaseId
    ): Collection
    {
        return $this->model
            ->where(
                'purchase_id',
                $purchaseId
            )
            ->get();
    }

    /**
     * آیتم‌های یک محصول
     */
    public function getByItem(
        string $itemType,
        int $itemId
    ): Collection
    {
        return $this->model
            ->where(
                'item_type',
                $itemType
            )
            ->where(
                'item_id',
                $itemId
            )
            ->get();
    }

    /**
     * ثبت آیتم خرید
     */
    public function createItem(
        array $data
    ): PurchaseItem
    {
        return $this->model->create(
            $data
        );
    }
}
