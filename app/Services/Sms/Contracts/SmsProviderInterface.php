<?php

namespace App\Services\Sms\Contracts;

interface SmsProviderInterface
{
    /**
     * ارسال کد تأیید (OTP)
     */
    public function sendOtp(
        string $mobile,
        string $code
    ): bool;

    /**
     * ارسال پیامک عادی
     */
    public function send(
        string $mobile,
        string $message
    ): bool;
}
