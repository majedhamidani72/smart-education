<?php

namespace App\Services;

use Throwable;
use App\Models\Purchase;
use App\Models\Plan;
use App\Models\Book;
use App\Models\Grade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
    /**
     * ساخت خرید از روی یک یا چند پلن.
     * --------------------------------------------------------------------
     * تنها راه معتبر ساخت خرید — قیمت‌ها و شماره‌ی فاکتور همیشه
     * همین‌جا (سمت سرور) از روی خودِ پلن محاسبه می‌شوند، نه از
     * روی چیزی که کلاینت فرستاده؛ این‌طوری هیچ کلاینتی نمی‌تواند
     * قیمت را دستکاری کند.
     *
     * @param  array<int>  $planIds
     */
    public function createFromPlans(
        int $userId,
        array $planIds,
        ?string $notes = null
    ): Purchase {

        return DB::transaction(function () use ($userId, $planIds, $notes) {

            $plans = Plan::query()
                ->whereIn('id', $planIds)
                ->where('is_active', true)
                ->get();

            if ($plans->isEmpty()) {

                throw new \InvalidArgumentException(
                    'هیچ پلن فعالی با این شناسه‌ها یافت نشد.'
                );
            }

            $totalAmount = 0;

            $discountAmount = 0;

            foreach ($plans as $plan) {

                $totalAmount += $plan->price;

                $discountAmount += $plan->discountAmount();
            }

            $purchase = $this->repository->create([

                'user_id' => $userId,

                // شماره‌ی فاکتور یکتا و خودکار — دیگر از کلاینت
                // گرفته نمی‌شود.
                'invoice_number' => 'INV-'
                    .now()->format('YmdHis')
                    .'-'
                    .random_int(100, 999),

                'total_amount' => $totalAmount,

                'discount_amount' => $discountAmount,

                'payable_amount' => $totalAmount - $discountAmount,

                'status' => 'pending',

                'notes' => $notes,

            ]);

            foreach ($plans as $plan) {

                $purchase->items()->create([

                    'plan_id' => $plan->id,

                    // نوع آیتم از روی چیزی که پلن واقعاً به آن
                    // وصل است (planable) تشخیص داده می‌شود؛ کتاب
                    // برای پایه‌های ۷ تا ۱۲، پایه برای ۱ تا ۶.
                    'item_type' => match ($plan->planable_type) {
                        Book::class => 'book',
                        Grade::class => 'grade',
                        default => 'package',
                    },

                    'item_id' => $plan->planable_id,

                    'title' => $plan->title,

                    'price' => $plan->price,

                    'discount_amount' => $plan->discountAmount(),

                    'final_price' => $plan->finalPrice(),

                    'quantity' => 1,

                ]);
            }

            return $purchase->fresh('items');
        });
    }

    /**
     * ساخت مستقیم (فقط برای استفاده‌ی داخلی/مدیریتی — نه از
     * طریق API عمومی کاربران).
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
