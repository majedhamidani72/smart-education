<?php

namespace App\Services\Payment;

use Throwable;
use App\Models\Purchase;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

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
     * درگاه پرداخت
     */
    protected PaymentGatewayInterface $gateway;

    public function __construct(
        PaymentTransactionRepositoryInterface $transactionRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        PaymentGatewayInterface $gateway
    ) {
        $this->transactionRepository = $transactionRepository;

        $this->subscriptionRepository = $subscriptionRepository;

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
}
