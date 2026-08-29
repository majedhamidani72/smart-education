<?php

namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * اتصال واقعی به کاوه‌نگار (kavenegar.com)
 * --------------------------------------------------------------------
 * برای فعال‌شدن، فقط کافی است در AppServiceProvider، binding مربوط
 * به SmsProviderInterface را از MockSmsProvider به همین کلاس تغییر
 * دهید و مقادیر واقعی را در .env بگذارید:
 *
 *   KAVENEGAR_API_KEY=...
 *   KAVENEGAR_SENDER=...              (سرشماره‌ی شما — اختیاری،
 *                                       اگر خالی باشد، سرشماره‌ی
 *                                       پیش‌فرض حساب استفاده می‌شود)
 *   KAVENEGAR_OTP_TEMPLATE=...        (نام الگویی که در پنل
 *                                       کاوه‌نگار برای OTP ساخته‌اید)
 *
 * دو سرویس متفاوت کاوه‌نگار استفاده می‌شود:
 * ۱) verify/lookup — مخصوص OTP، از طریق یک «الگوی» از پیش تعریف‌
 *    شده در پنل کاوه‌نگار (نه متن آزاد) — سریع‌تر و ارزان‌تر است.
 * ۲) sms/send — پیامک عادی با متن آزاد، برای بقیه‌ی اطلاع‌رسانی‌ها
 *    (تایید خرید، تسویه، و غیره).
 */
class KavenegarProvider implements SmsProviderInterface
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('services.kavenegar.api_key');

        $this->baseUrl = 'https://api.kavenegar.com/v1/'.$this->apiKey.'/';
    }

    public function sendOtp(
        string $mobile,
        string $code
    ): bool {

        $template = config('services.kavenegar.otp_template');

        if (blank($template)) {

            return $this->send(
                $mobile,
                "کد تایید تغییر رمز عبور درسکا: {$code}\nاعتبار کد: ۲ دقیقه"
            );

        }

        try {

            $response = Http::timeout(15)->get($this->baseUrl.'verify/lookup.json', [

                'receptor' => $mobile,

                'token' => $code,

                'template' => $template,

                'type' => 'sms',

            ]);

            if (! $response->successful()) {

                Log::error('Kavenegar OTP failed.', [
                    'mobile' => $mobile,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;

        } catch (\Throwable $e) {

            Log::error('Kavenegar OTP exception.', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function send(
        string $mobile,
        string $message
    ): bool {

        $sender = config('services.kavenegar.sender');

        try {

            $response = Http::timeout(15)->get($this->baseUrl.'sms/send.json', array_filter([

                'receptor' => $mobile,

                'message' => $message,

                'sender' => $sender,

            ]));

            if (! $response->successful()) {

                Log::error('Kavenegar SMS failed.', [
                    'mobile' => $mobile,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;

        } catch (\Throwable $e) {

            Log::error('Kavenegar SMS exception.', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
