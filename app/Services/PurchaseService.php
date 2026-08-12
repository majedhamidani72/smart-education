<?php

namespace App\Services;

use Throwable;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;

class PurchaseService
{
    /**
     * Repository
     */
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
     * صفحه‌بندی خریدها
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
        try {

            return $this->repository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Purchase creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * ویرایش خرید
     */
    public function update(
        Purchase $purchase,
        array $data
    ): Purchase
    {
        try {

            return $this->repository->update(

                $purchase,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Purchase update failed.', [

                'purchase_id' => $purchase->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف خرید
     */
    public function delete(
        Purchase $purchase
    ): bool
    {
        try {

            return $this->repository->delete(
                $purchase
            );

        } catch (Throwable $e) {

            Log::error('Purchase delete failed.', [

                'purchase_id' => $purchase->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
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
     * ثبت پرداخت موفق
     */
    public function markAsPaid(
        Purchase $purchase
    ): Purchase
    {
        try {

            return $this->repository->markAsPaid(
                $purchase
            );

        } catch (Throwable $e) {

            Log::error('Purchase mark as paid failed.', [

                'purchase_id' => $purchase->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * ثبت پرداخت ناموفق
     */
    public function markAsFailed(
        Purchase $purchase
    ): Purchase
    {
        try {

            return $this->repository->markAsFailed(
                $purchase
            );

        } catch (Throwable $e) {

            Log::error('Purchase mark as failed failed.', [

                'purchase_id' => $purchase->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * لغو خرید
     */
    public function markAsCancelled(
        Purchase $purchase
    ): Purchase
    {
        try {

            return $this->repository->markAsCancelled(
                $purchase
            );

        } catch (Throwable $e) {

            Log::error('Purchase cancellation failed.', [

                'purchase_id' => $purchase->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * بازگشت وجه
     */
    public function markAsRefunded(
        Purchase $purchase
    ): Purchase
    {
        try {

            return $this->repository->markAsRefunded(
                $purchase
            );

        } catch (Throwable $e) {

            Log::error('Purchase refund failed.', [

                'purchase_id' => $purchase->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
