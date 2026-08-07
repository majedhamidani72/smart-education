<?php

namespace App\Services\Payment\Contracts;

use App\Models\PaymentTransaction;

interface PaymentGatewayInterface
{
    /**
     * ایجاد درخواست پرداخت
     */
    public function requestPayment(
        PaymentTransaction $transaction
    ): array;

    /**
     * بررسی و تایید پرداخت
     */
    public function verifyPayment(
        PaymentTransaction $transaction,
        array $data
    ): array;

    /**
     * بازگشت وجه
     */
    public function refundPayment(
        PaymentTransaction $transaction
    ): array;
}
