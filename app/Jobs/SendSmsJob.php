<?php

namespace App\Jobs;

use App\Services\Sms\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ارسال یک پیامک اطلاع‌رسانی عادی (غیر از OTP) — از طریق صف، تا
 * تاخیر یا خطای احتمالی سرویس پیامک هیچ‌وقت باعث کندشدن یا
 * شکست خودِ عملیات اصلی (خرید، تسویه، ساخت حساب و ...) نشود.
 */
class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $mobile,
        public string $message
    ) {
    }

    public function handle(SmsService $smsService): void
    {
        $smsService->send(
            $this->mobile,
            $this->message
        );
    }
}
