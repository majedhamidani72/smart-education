<?php

namespace App\Repositories\Interfaces;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * پیدا کردن با شماره فاکتور
     */
    public function findByInvoiceNumber(
        string $invoiceNumber
    ): ?Purchase;

    /**
     * خریدهای یک کاربر
     */
    public function getByUser(
        int $userId
    ): Collection;

    /**
     * خریدهای در انتظار
     */
    public function getPending(): Collection;

    /**
     * خریدهای پرداخت شده
     */
    public function getPaid(): Collection;

    /**
     * ثبت پرداخت موفق
     */
    public function markAsPaid(
        Purchase $purchase
    ): Purchase;

    /**
     * ثبت پرداخت ناموفق
     */
    public function markAsFailed(
        Purchase $purchase
    ): Purchase;

    /**
     * لغو خرید
     */
    public function markAsCancelled(
        Purchase $purchase
    ): Purchase;

    /**
     * بازگشت وجه
     */
    public function markAsRefunded(
        Purchase $purchase
    ): Purchase;
}
