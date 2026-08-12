<?php

namespace App\Services;

use Throwable;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;

class PurchaseItemService
{
    /**
     * Repository آیتم‌های خرید
     */
    protected PurchaseItemRepositoryInterface $repository;

    public function __construct(
        PurchaseItemRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * همه آیتم‌ها
     */
    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * صفحه‌بندی آیتم‌های خرید
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->repository->paginate(
            $perPage
        );
    }

    /**
     * یک آیتم
     */
    public function findById(
        int $id
    ): ?PurchaseItem
    {
        return $this->repository->findById(
            $id
        );
    }

    /**
     * آیتم‌های یک خرید
     */
    public function getByPurchase(
        int $purchaseId
    ): Collection
    {
        return $this->repository->getByPurchase(
            $purchaseId
        );
    }

    /**
     * آیتم‌های یک محصول
     */
    public function getByItem(
        string $itemType,
        int $itemId
    ): Collection
    {
        return $this->repository->getByItem(
            $itemType,
            $itemId
        );
    }

    /**
     * ثبت آیتم خرید
     */
    public function create(
        array $data
    ): PurchaseItem
    {
        try {

            return $this->repository->createItem(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Purchase item creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * ویرایش آیتم
     */
    public function update(
        PurchaseItem $purchaseItem,
        array $data
    ): PurchaseItem
    {
        try {

            return $this->repository->update(

                $purchaseItem,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Purchase item update failed.', [

                'purchase_item_id' => $purchaseItem->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف آیتم
     */
    public function delete(
        PurchaseItem $purchaseItem
    ): bool
    {
        try {

            return $this->repository->delete(
                $purchaseItem
            );

        } catch (Throwable $e) {

            Log::error('Purchase item delete failed.', [

                'purchase_item_id' => $purchaseItem->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
