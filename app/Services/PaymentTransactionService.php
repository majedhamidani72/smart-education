<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;

class PaymentTransactionService
{
    protected PaymentTransactionRepositoryInterface $repository;

    public function __construct(
        PaymentTransactionRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * ایجاد تراکنش
     */
    public function create(
        array $data
    ): PaymentTransaction
    {
        return $this->repository->create(
            $data
        );
    }

    /**
     * بروزرسانی تراکنش
     */
    public function update(
        PaymentTransaction $transaction,
        array $data
    ): bool
    {
        return $this->repository->update(
            $transaction,
            $data
        );
    }

    /**
     * حذف تراکنش
     */
    public function delete(
        PaymentTransaction $transaction
    ): bool
    {
        return $this->repository->delete(
            $transaction
        );
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
        return $this->repository->markAsPaid(
            $transaction,
            $data
        );
    }

    /**
     * ثبت پرداخت ناموفق
     */
    public function markAsFailed(
        PaymentTransaction $transaction,
        ?string $message = null
    ): bool
    {
        return $this->repository->markAsFailed(
            $transaction,
            $message
        );
    }

    /**
     * ثبت بازگشت وجه
     */
    public function markAsRefunded(
        PaymentTransaction $transaction
    ): bool
    {
        return $this->repository->markAsRefunded(
            $transaction
        );
    }
}
