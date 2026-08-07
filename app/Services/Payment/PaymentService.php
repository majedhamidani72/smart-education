<?php

namespace App\Services\Payment;

use App\Models\Purchase;
use App\Models\PaymentTransaction;
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

            'gateway' => 'zibal',

            'amount' => $purchase->price,

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

        return $this->gateway
            ->requestPayment(
                $transaction
            );

    }

    /**
     * تایید پرداخت
     */
    public function verifyPayment(
        PaymentTransaction $transaction,
        array $data
    ): array {

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

    }

    /**
     * بازگشت وجه
     */
    public function refund(
        PaymentTransaction $transaction
    ): array {

        return $this->gateway
            ->refundPayment(
                $transaction
            );

    }

}
