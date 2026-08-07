<?php

namespace App\Services\Sms;

use App\Services\Sms\Contracts\SmsProviderInterface;

class SmsService
{
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
    ): bool {

        return $this->provider->sendOtp(
            $mobile,
            $code
        );
    }
}
