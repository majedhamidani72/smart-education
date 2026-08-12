<?php

namespace App\Services\Sms;

use Throwable;
use Illuminate\Support\Facades\Log;
use App\Services\Sms\Contracts\SmsProviderInterface;

class SmsService
{
    /**
     * ارائه‌دهنده پیامک
     */
    protected SmsProviderInterface $provider;

    public function __construct(
        SmsProviderInterface $provider
    ) {
        $this->provider = $provider;
    }

    /**
     * ارسال OTP
     */
    public function sendOtp(
        string $mobile,
        string $code
    ): bool
    {
        try {

            return $this->provider->sendOtp(

                $mobile,

                $code

            );

        } catch (Throwable $e) {

            Log::error('SMS OTP failed.', [

                'mobile' => $mobile,

                'error' => $e->getMessage(),

            ]);

            return false;
        }
    }

    /**
     * ارسال پیامک عادی
     */
    public function send(
        string $mobile,
        string $message
    ): bool
    {
        try {

            return $this->provider->send(

                $mobile,

                $message

            );

        } catch (Throwable $e) {

            Log::error('SMS sending failed.', [

                'mobile' => $mobile,

                'error' => $e->getMessage(),

            ]);

            return false;
        }
    }
}
