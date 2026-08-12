<?php

namespace App\Services;

use Throwable;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;

class PaymentTransactionService
{
    /**
     * Repository تراکنش‌ها
     */
    protected PaymentTransactionRepositoryInterface $repository;

    public function __construct(
        PaymentTransactionRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * دریافت همه تراکنش‌ها
     */
    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * صفحه‌بندی تراکنش‌ها
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
     * ایجاد تراکنش
     */
    public function create(
        array $data
    ): PaymentTransaction
    {
        try {

            return $this->repository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Payment transaction creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * بروزرسانی تراکنش
     */
    public function update(
        PaymentTransaction $transaction,
        array $data
    ): PaymentTransaction
    {
        try {

            return $this->repository->update(

                $transaction,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Payment transaction update failed.', [

                'transaction_id' => $transaction->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف تراکنش
     */
    public function delete(
        PaymentTransaction $transaction
    ): bool
    {
        try {

            return $this->repository->delete(
                $transaction
            );

        } catch (Throwable $e) {

            Log::error('Payment transaction delete failed.', [

                'transaction_id' => $transaction->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * دریافت تراکنش
     */
    public function findById(
        int $id
    ): ?PaymentTransaction
    {
        return $this->repository->findById(
            $id
        );
    }

    /**
     * جستجو با Authority
     */
    public function findByAuthority(
        string $authority
    ): ?PaymentTransaction
    {
        return $this->repository->findByAuthority(
            $authority
        );
    }

    /**
     * جستجو با Reference ID
     */
    public function findByReferenceId(
        string $referenceId
    ): ?PaymentTransaction
    {
        return $this->repository->findByReferenceId(
            $referenceId
        );
    }

    /**
     * تراکنش‌های کاربر
     */
    public function getUserTransactions(
        int $userId
    ): Collection
    {
        return $this->repository->getUserTransactions(
            $userId
        );
    }

    /**
     * تراکنش‌های خرید
     */
    public function getPurchaseTransactions(
        int $purchaseId
    ): Collection
    {
        return $this->repository->getPurchaseTransactions(
            $purchaseId
        );
    }

    /**
     * ثبت پرداخت موفق
     */
    public function markAsPaid(
        PaymentTransaction $transaction,
        array $data
    ): bool
    {
        try {

            return $this->repository->markAsPaid(

                $transaction,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Mark payment as paid failed.', [

                'transaction_id' => $transaction->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * ثبت پرداخت ناموفق
     */
    public function markAsFailed(
        PaymentTransaction $transaction,
        ?string $message = null
    ): bool
    {
        try {

            return $this->repository->markAsFailed(

                $transaction,

                $message

            );

        } catch (Throwable $e) {

            Log::error('Mark payment as failed failed.', [

                'transaction_id' => $transaction->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * ثبت بازگشت وجه
     */
    public function markAsRefunded(
        PaymentTransaction $transaction
    ): bool
    {
        try {

            return $this->repository->markAsRefunded(
                $transaction
            );

        } catch (Throwable $e) {

            Log::error('Mark payment as refunded failed.', [

                'transaction_id' => $transaction->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
