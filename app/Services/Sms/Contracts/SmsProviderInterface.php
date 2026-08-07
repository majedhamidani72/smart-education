<?php

namespace App\Services\Sms\Contracts;

interface SmsProviderInterface
{
    /**
     * ارسال پیامک
     */
    public function sendOtp(
        string $mobile,
        string $code
    ): bool;



}
