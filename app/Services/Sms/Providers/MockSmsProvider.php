<?php

namespace App\Services\Sms\Providers;

use Illuminate\Support\Facades\Log;
use App\Services\Sms\Contracts\SmsProviderInterface;

class MockSmsProvider implements SmsProviderInterface
{
    /**
     * ارسال آزمایشی کد تأیید (OTP)
     */
    public function sendOtp(
        string $mobile,
        string $code
    ): bool
    {
        Log::info('Mock OTP SMS', [

            'mobile' => $mobile,

            'otp' => $code,

        ]);

        return true;
    }

    /**
     * ارسال آزمایشی پیامک
     */
    public function send(
        string $mobile,
        string $message
    ): bool
    {
        Log::info('Mock SMS', [

            'mobile' => $mobile,

            'message' => $message,

        ]);

        return true;
    }
}
