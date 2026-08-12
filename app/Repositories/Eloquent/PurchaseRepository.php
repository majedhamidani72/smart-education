<?php

namespace App\Repositories\Eloquent;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;

class PurchaseRepository extends BaseRepository implements PurchaseRepositoryInterface
{
    public function __construct(
        Purchase $model
    ) {
        parent::__construct($model);
    }

    /**
     * پیدا کردن بر اساس شماره فاکتور
     */
    public function findByInvoiceNumber(
        string $invoiceNumber
    ): ?Purchase
    {
        return $this->model
            ->where(
                'invoice_number',
                $invoiceNumber
            )
            ->first();
    }

    /**
     * خریدهای یک کاربر
     */
    public function getByUser(
        int $userId
    ): Collection
    {
        return $this->model
            ->where(
                'user_id',
                $userId
            )
            ->latest()
            ->get();
    }

    /**
     * پرداخت‌های در انتظار
     */
    public function getPending(): Collection
    {
        return $this->model
            ->where(
                'status',
                'pending'
            )
            ->latest()
            ->get();
    }

    /**
     * پرداخت‌های موفق
     */
    public function getPaid(): Collection
    {
        return $this->model
            ->where(
                'status',
                'paid'
            )
            ->latest()
            ->get();
    }

    /**
     * ثبت پرداخت موفق
     */
    public function markAsPaid(
        Purchase $purchase
    ): Purchase
    {
        $purchase->update([

            'status'  => 'paid',

            'paid_at' => now(),

        ]);

        return $purchase->fresh();
    }

    /**
     * ثبت پرداخت ناموفق
     */
    public function markAsFailed(
        Purchase $purchase
    ): Purchase
    {
        $purchase->update([

            'status' => 'failed',

        ]);

        return $purchase->fresh();
    }

    /**
     * لغو خرید
     */
    public function markAsCancelled(
        Purchase $purchase
    ): Purchase
    {
        $purchase->update([

            'status' => 'cancelled',

        ]);

        return $purchase->fresh();
    }

    /**
     * ثبت بازگشت وجه
     */
    public function markAsRefunded(
        Purchase $purchase
    ): Purchase
    {
        $purchase->update([

            'status' => 'refunded',

        ]);

        return $purchase->fresh();
    }
}
