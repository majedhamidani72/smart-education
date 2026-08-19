<?php

namespace App\Services\Payment;

use Throwable;
use App\Models\Purchase;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;

class PaymentService
{
    /**
     * Repository تراکنش‌ها
     */
    protected PaymentTransactionRepositoryInterface $transactionRepository;

    /**
     * درگاه پرداخت
     */
    protected PaymentGatewayInterface $gateway;

    public function __construct(
        PaymentTransactionRepositoryInterface $transactionRepository,
        PaymentGatewayInterface $gateway
    ) {
        $this->transactionRepository = $transactionRepository;

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
}
