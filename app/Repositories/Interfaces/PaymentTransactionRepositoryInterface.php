<?php

namespace App\Repositories\Interfaces;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Collection;

interface PaymentTransactionRepositoryInterface
{
    /**
     * ایجاد تراکنش
     */
    public function create(
        array $data
    ): PaymentTransaction;

    /**
     * بروزرسانی تراکنش
     */
    public function update(
        PaymentTransaction $transaction,
        array $data
    ): bool;

    /**
     * حذف تراکنش
     */
    public function delete(
        PaymentTransaction $transaction
    ): bool;

    /**
     * دریافت تراکنش با شناسه
     */
    public function findById(
        int $id
    ): ?PaymentTransaction;

    /**
     * پیدا کردن با Authority
     */
    public function findByAuthority(
        string $authority
    ): ?PaymentTransaction;

    /**
     * پیدا کردن با Reference ID
     */
    public function findByReferenceId(
        string $referenceId
    ): ?PaymentTransaction;

    /**
     * دریافت تراکنش‌های کاربر
     */
    public function getUserTransactions(
        int $userId
    ): Collection;

    /**
     * دریافت تراکنش‌های خرید
     */
    public function getPurchaseTransactions(
        int $purchaseId
    ): Collection;

    /**
     * علامت‌گذاری پرداخت موفق
     */
    public function markAsPaid(
        PaymentTransaction $transaction,
        array $data
    ): bool;

    /**
     * علامت‌گذاری پرداخت ناموفق
     */
    public function markAsFailed(
        PaymentTransaction $transaction,
        ?string $message = null
    ): bool;

    /**
     * ثبت بازگشت وجه
     */
    public function markAsRefunded(
        PaymentTransaction $transaction
    ): bool;
}
