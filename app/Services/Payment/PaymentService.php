<?php

namespace App\Services\Payment;

use Throwable;
use App\Models\Purchase;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\Book;
use App\Models\Grade;
use App\Models\AppGradeSubject;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Repositories\Interfaces\TeacherEarningRepositoryInterface;

class PaymentService
{
    /**
     * Repository تراکنش‌ها
     */
    protected PaymentTransactionRepositoryInterface $transactionRepository;

    /**
     * Repository اشتراک‌ها (دسترسی)
     */
    protected SubscriptionRepositoryInterface $subscriptionRepository;

    /**
     * Repository درآمد معلمان
     */
    protected TeacherEarningRepositoryInterface $teacherEarningRepository;

    /**
     * درگاه پرداخت
     */
    protected PaymentGatewayInterface $gateway;

    public function __construct(
        PaymentTransactionRepositoryInterface $transactionRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        TeacherEarningRepositoryInterface $teacherEarningRepository,
        PaymentGatewayInterface $gateway
    ) {
        $this->transactionRepository = $transactionRepository;

        $this->subscriptionRepository = $subscriptionRepository;

        $this->teacherEarningRepository = $teacherEarningRepository;

        $this->gateway = $gateway;
    }

    /**
     * ایجاد تراکنش جدید
     */
    public function createTransaction(
        Purchase $purchase
    ): PaymentTransaction {
        return $this->transactionRepository->create([

            'purchase_id' => $purchase->id,

            'user_id' => $purchase->user_id,

            'gateway' => env('PAYMENT_DEFAULT', 'zibal'),
            
            // توجه: ستون درست روی جدول purchases خودِ «payable_amount»
            // است، نه «price» (که اصلاً چنین ستونی وجود ندارد و
            // همیشه NULL برمی‌گشت).
            'amount' => $purchase->payable_amount,

            'currency' => 'IRT',

            'status' => 'pending',

        ]);
    }

    /**
     * شروع فرآیند پرداخت
     */
    public function requestPayment(
        PaymentTransaction $transaction
    ): array {
        try {

            return $this->gateway
                ->requestPayment(
                    $transaction
                );
        } catch (Throwable $e) {

            Log::error('Payment request failed.', [

                'transaction_id' => $transaction->id,

                'error' => $e->getMessage(),

            ]);

            return [

                'success' => false,

                'message' => 'Payment request failed.',

            ];
        }
    }

    /**
     * تایید پرداخت
     */
    public function verifyPayment(
        PaymentTransaction $transaction,
        array $data
    ): array {
        try {

            $result = $this->gateway
                ->verifyPayment(
                    $transaction,
                    $data
                );

            if (
                isset($result['success'])
                &&
                $result['success'] === true
            ) {

                $this->transactionRepository
                    ->markAsPaid(

                        $transaction,

                        $result

                    );

                // نکته‌ی مهم: تا اینجا فقط رکورد «تراکنش» به‌روز
                // می‌شد، ولی خودِ «خرید» (Purchase) که کاربر واقعاً
                // بر اساس آن به محتوا دسترسی پیدا می‌کند، دست‌نخورده
                // می‌ماند و همیشه «در انتظار پرداخت» باقی می‌ماند.
                $transaction->purchase()->update([

                    'status' => 'paid',

                    'paid_at' => now(),

                ]);

                // به ازای هر آیتم خرید که به یک «پلن» وصل است، یک
                // رکورد Subscription (دسترسی) ساخته می‌شود — این
                // همان چیزی است که واقعاً به دانش‌آموز اجازه‌ی
                // استفاده می‌دهد. توجه: خودِ Plan مشخص می‌کند این
                // دسترسی به یک «پایه‌ی کامل» تعلق دارد (پایه‌های
                // ۱ تا ۶) یا به یک «کتاب مشخص» (پایه‌های ۷ تا ۱۲) —
                // چون planable در جدول plans چندریختی است و می‌تواند
                // به هرکدام وصل شود؛ این تصمیم از پیش در Plan تعریف
                // شده، نه اینجا.
                $this->grantAccessFromPurchase($transaction->purchase);

                // به همون ترتیب که دسترسی داده می‌شود، سهم معلم(ها)
                // از این فروش هم محاسبه و ثبت می‌شود.
                $this->createTeacherEarnings($transaction->purchase);

            } else {

                $this->transactionRepository
                    ->markAsFailed(

                        $transaction,

                        $result['message'] ?? null

                    );
            }

            return $result;
        } catch (Throwable $e) {

            Log::error('Payment verification failed.', [

                'transaction_id' => $transaction->id,

                'error' => $e->getMessage(),

            ]);

            $this->transactionRepository
                ->markAsFailed(

                    $transaction,

                    $e->getMessage()

                );

            return [

                'success' => false,

                'message' => 'Payment verification failed.',

            ];
        }
    }

    /**
     * بازگشت وجه
     */
    public function refund(
        PaymentTransaction $transaction
    ): array {
        try {

            return $this->gateway
                ->refundPayment(
                    $transaction
                );
        } catch (Throwable $e) {

            Log::error('Refund failed.', [

                'transaction_id' => $transaction->id,

                'error' => $e->getMessage(),

            ]);

            return [

                'success' => false,

                'message' => 'Refund failed.',

            ];
        }
    }

    /**
     * ساخت رکوردهای دسترسی (Subscription) از روی آیتم‌های یک خرید
     * پرداخت‌شده.
     * --------------------------------------------------------------------
     * قبل از این متد، این بخش کامل جا افتاده بود: پرداخت با
     * موفقیت ثبت می‌شد ولی هیچ‌جا واقعاً به دانش‌آموز دسترسی داده
     * نمی‌شد — یعنی خرید می‌کرد ولی هیچی برایش باز نمی‌شد.
     */
    protected function grantAccessFromPurchase(
        Purchase $purchase
    ): void {

        foreach ($purchase->items as $item) {

            // آیتم‌هایی که به هیچ پلنی وصل نیستند (اگر چنین چیزی
            // وجود داشته باشد) دسترسی‌ای برای ساختن ندارند.
            if (! $item->plan_id || ! $item->plan) {
                continue;
            }

            $plan = $item->plan;

            // duration_days خالی یعنی دسترسی «دائمی». چون ستون
            // expires_at در دیتابیس nullable نیست، به‌جای NULL از
            // یک تاریخ خیلی دور (۱۰۰ سال بعد) به‌عنوان «همیشگی»
            // استفاده می‌شود — یک قرارداد رایج برای این وضعیت.
            $expiresAt = $plan->duration_days
                ? now()->addDays($plan->duration_days)
                : now()->addYears(100);

            $this->subscriptionRepository->create([

                'user_id' => $purchase->user_id,

                'purchase_id' => $purchase->id,

                'plan_id' => $plan->id,

                'status' => 'active',

                'starts_at' => now(),

                'expires_at' => $expiresAt,

            ]);
        }
    }

    /**
     * محاسبه و ثبت سهم معلم(ها) از یک خرید پرداخت‌شده.
     * --------------------------------------------------------------------
     * برای «خرید یک کتاب»: معلمِ همان کتاب (طبق TeacherAssignment)
     * طبق درصد خودش سهم می‌گیرد.
     *
     * برای «خرید کل پایه»: چون ممکن است چند معلم مختلف کتاب‌های
     * این پایه را داشته باشند، مبلغ به‌طور مساوی بین کتاب‌هایی که
     * معلم فعال دارند تقسیم می‌شود؛ سهم هر معلم از سهمِ کتابِ
     * خودش، طبق درصد خودش محاسبه می‌شود. این یک پیش‌فرض منطقی
     * است — طبق تصمیم پروژه، اگر لازم شد، مدیر می‌تواند بعداً
     * دستی از پنل «درآمد معلمان» عدد را اصلاح کند.
     */
    protected function createTeacherEarnings(
        Purchase $purchase
    ): void {

        foreach ($purchase->items as $item) {

            if (! $item->plan_id || ! $item->plan) {
                continue;
            }

            $plan = $item->plan;

            if ($plan->planable_type === Book::class) {

                $assignment = TeacherAssignment::where('book_id', $plan->planable_id)
                    ->where('is_active', true)
                    ->first();

                if (! $assignment) {
                    continue;
                }

                $this->recordEarning(
                    $assignment,
                    $purchase,
                    $item,
                    $item->final_price
                );

                continue;
            }

            if ($plan->planable_type === Grade::class) {

                $bookIds = Book::query()
                    ->whereHas(
                        'appGradeSubject',
                        fn($q) => $q->where('grade_id', $plan->planable_id)
                    )
                    ->pluck('id');

                $assignments = TeacherAssignment::whereIn('book_id', $bookIds)
                    ->where('is_active', true)
                    ->get();

                if ($assignments->isEmpty()) {
                    continue;
                }

                // مبلغ به‌طور مساوی بین کتاب‌هایی که معلم فعال
                // دارند تقسیم می‌شود (نه بین همه‌ی کتاب‌های پایه،
                // چون کتاب بدون معلم سهمی برای تقسیم ندارد).
                $sharePerBook = intdiv(
                    $item->final_price,
                    $assignments->count()
                );

                foreach ($assignments as $assignment) {

                    $this->recordEarning(
                        $assignment,
                        $purchase,
                        $item,
                        $sharePerBook
                    );
                }
            }
        }
    }

    /**
     * ثبت یک رکورد درآمد برای یک معلم مشخص.
     */
    protected function recordEarning(
        TeacherAssignment $assignment,
        Purchase $purchase,
        \App\Models\PurchaseItem $item,
        int $saleAmount
    ): void {

        $amount = (int) round(
            $saleAmount * $assignment->commission_percentage / 100
        );

        $this->teacherEarningRepository->create([

            'teacher_id' => $assignment->teacher_id,

            'purchase_id' => $purchase->id,

            'purchase_item_id' => $item->id,

            'sale_amount' => $saleAmount,

            'percentage' => $assignment->commission_percentage,

            'amount' => $amount,

            'status' => 'pending',

        ]);
    }
}
