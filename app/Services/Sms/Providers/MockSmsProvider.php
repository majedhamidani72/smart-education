<?php

namespace App\Services\Sms\Providers;

use Illuminate\Support\Facades\Log;
use App\Services\Sms\Contracts\SmsProviderInterface;

class MockSmsProvider implements SmsProviderInterface
{
    /**
     * ارسال آزمایشی OTP
     */
    public function sendOtp(
        string $mobile,
        string $code
    ): bool {

        Log::info('Mock SMS', [

            'mobile' => $mobile,

            'otp' => $code,

        ]);

        return true;
    }

//شش ماه بعد اگر قاصدک خریدی

// فقط این فایل را اضافه می‌کنیم:

// app/Services/Sms/Providers/GhasedakSmsProvider.php

// و فقط یک خط در AppServiceProvider تغییر می‌کند:
}
