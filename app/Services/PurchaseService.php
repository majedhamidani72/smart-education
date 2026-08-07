<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;

class PurchaseService
{
    protected PurchaseRepositoryInterface $repository;

    public function __construct(
        PurchaseRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * همه خریدها
     */
    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * خریدهای یک کاربر
     */
    public function getByUser(
        int $userId
    ): Collection
    {
        return $this->repository->getByUser(
            $userId
        );
    }

    /**
     * دریافت یک خرید
     */
    public function findById(
        int $id
    ): ?Purchase
    {
        return $this->repository->findById(
            $id
        );
    }

    /**
     * پیدا کردن با شماره فاکتور
     */
    public function findByInvoiceNumber(
        string $invoiceNumber
    ): ?Purchase
    {
        return $this->repository->findByInvoiceNumber(
            $invoiceNumber
        );
    }

    /**
     * ثبت خرید
     */
    public function create(
        array $data
    ): Purchase
    {
        return $this->repository->create(
            $data
        );
    }

    /**
     * ویرایش خرید
     */
    public function update(
        Purchase $purchase,
        array $data
    ): Purchase
    {
        return $this->repository->update(
            $purchase,
            $data
        );
    }

    /**
     * حذف خرید
     */
    public function delete(
        Purchase $purchase
    ): bool
    {
        return $this->repository->delete(
            $purchase
        );
    }

    /**
     * پرداخت‌های در انتظار
     */
    public function pending(): Collection
    {
        return $this->repository->getPending();
    }

    /**
     * پرداخت‌های موفق
     */
    public function paid(): Collection
    {
        return $this->repository->getPaid();
    }

    /**
     * پرداخت موفق
     */
    public function markAsPaid(
        int $purchaseId
    ): Purchase
    {
        return $this->repository->markAsPaid(
            $purchaseId
        );
    }

    /**
     * پرداخت ناموفق
     */
    public function markAsFailed(
        int $purchaseId
    ): Purchase
    {
        return $this->repository->markAsFailed(
            $purchaseId
        );
    }

    /**
     * لغو خرید
     */
    public function markAsCancelled(
        int $purchaseId
    ): Purchase
    {
        return $this->repository->markAsCancelled(
            $purchaseId
        );
    }

    /**
     * بازگشت وجه
     */
    public function markAsRefunded(
        int $purchaseId
    ): Purchase
    {
        return $this->repository->markAsRefunded(
            $purchaseId
        );
    }
}
