<?php

namespace App\Repositories\Eloquent;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;

class PaymentTransactionRepository implements PaymentTransactionRepositoryInterface
{
    protected PaymentTransaction $model;

    public function __construct(
        PaymentTransaction $model
    ) {
        $this->model = $model;
    }

    /**
     * ایجاد تراکنش
     */
    public function create(
        array $data
    ): PaymentTransaction {

        return $this->model->create(
            $data
        );

    }

    /**
     * بروزرسانی تراکنش
     */
    public function update(
        PaymentTransaction $transaction,
        array $data
    ): bool {

        return $transaction->update(
            $data
        );

    }

    /**
     * حذف تراکنش
     */
    public function delete(
        PaymentTransaction $transaction
    ): bool {

        return $transaction->delete();

    }

    /**
     * پیدا کردن با شناسه
     */
    public function findById(
        int $id
    ): ?PaymentTransaction {

        return $this->model->find($id);

    }

    /**
     * پیدا کردن با Authority
     */
    public function findByAuthority(
        string $authority
    ): ?PaymentTransaction {

        return $this->model
            ->where('authority', $authority)
            ->first();

    }

    /**
     * پیدا کردن با Reference ID
     */
    public function findByReferenceId(
        string $referenceId
    ): ?PaymentTransaction {

        return $this->model
            ->where('reference_id', $referenceId)
            ->first();

    }

    /**
     * تراکنش‌های کاربر
     */
    public function getUserTransactions(
        int $userId
    ): Collection {

        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->get();

    }

    /**
     * تراکنش‌های خرید
     */
    public function getPurchaseTransactions(
        int $purchaseId
    ): Collection {

        return $this->model
            ->where('purchase_id', $purchaseId)
            ->latest()
            ->get();

    }

    /**
     * پرداخت موفق
     */
    public function markAsPaid(
        PaymentTransaction $transaction,
        array $data
    ): bool {

        return $transaction->update([

            'status' => 'paid',

            'reference_id' => $data['reference_id'] ?? null,

            'transaction_id' => $data['transaction_id'] ?? null,

            'card_pan' => $data['card_pan'] ?? null,

            'gateway_response' => $data['gateway_response'] ?? null,

            'message' => $data['message'] ?? null,

            'paid_at' => now(),

            'verified_at' => now(),

        ]);

    }

    /**
     * پرداخت ناموفق
     */
    public function markAsFailed(
        PaymentTransaction $transaction,
        ?string $message = null
    ): bool {

        return $transaction->update([

            'status' => 'failed',

            'message' => $message,

        ]);

    }

    /**
     * بازگشت وجه
     */
    public function markAsRefunded(
        PaymentTransaction $transaction
    ): bool {

        return $transaction->update([

            'status' => 'refunded',

        ]);

    }
}
