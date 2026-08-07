<?php

namespace App\Services;

use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;

class PurchaseItemService
{
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
        return $this->repository->createItem(
            $data
        );
    }

    /**
     * ویرایش آیتم
     */
    public function update(
        PurchaseItem $purchaseItem,
        array $data
    ): PurchaseItem
    {
        return $this->repository->update(
            $purchaseItem,
            $data
        );
    }

    /**
     * حذف آیتم
     */
    public function delete(
        PurchaseItem $purchaseItem
    ): bool
    {
        return $this->repository->delete(
            $purchaseItem
        );
    }
}
