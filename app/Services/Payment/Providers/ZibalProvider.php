<?php

namespace App\Services\Payment\Providers;

use Illuminate\Support\Facades\Http;
use App\Models\PaymentTransaction;
use App\Services\Payment\Contracts\PaymentGatewayInterface;

/**
 * درگاه زیبال
 * --------------------------------------------------------------------
 * نکات مهم مطابق مستندات رسمی زیبال:
 *   ۱) زیبال مبلغ را به ریال می‌خواهد، ولی دیتابیس این پروژه
 *      تومان ذخیره می‌کند — تبدیل (×۱۰ رفت، ÷۱۰ برگشت) فقط همین‌جا
 *      انجام می‌شود، جای دیگری از پروژه لازم نیست نگرانش باشد.
 *   ۲) جواب زیبال کلید success ندارد؛ کلید result دارد (۱۰۰ یعنی
 *      موفق، ۲۰۱ یعنی این تراکنش قبلاً هم تایید شده). این کلاس
 *      این را به success بولی برای بقیه‌ی پروژه ترجمه می‌کند.
 *   ۳) trackId که زیبال در مرحله‌ی request برمی‌گرداند، همان‌جا
 *      روی ستون authority ذخیره می‌شود؛ چون Callback بعداً دقیقاً
 *      با همین ستون تراکنش را پیدا می‌کند.
 */
class ZibalProvider implements PaymentGatewayInterface
{
    /**
     * آدرس API زیبال
     */
    protected string $baseUrl;

    /**
     * مرچنت
     */
    protected string $merchant;

    /**
     * Callback
     */
    protected string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.zibal.base_url');

        $this->merchant = config('services.zibal.merchant');

        $this->callbackUrl = config('services.zibal.callback_url');
    }

    /**
     * ایجاد درخواست پرداخت
     */
    public function requestPayment(
        PaymentTransaction $transaction
    ): array {

        $response = Http::post(

            "{$this->baseUrl}/request",

            [

                'merchant' => $this->merchant,

                // تبدیل تومان (ذخیره‌شده در دیتابیس) به ریال
                // (چیزی که زیبال می‌خواهد).
                'amount' => $transaction->amount * 10,

                'callbackUrl' => $this->callbackUrl,

                'orderId' => (string) $transaction->id,

                'description' => 'خرید شماره '.$transaction->purchase_id,

            ]

        );

        $body = $response->json();

        $success = ($body['result'] ?? null) === 100;

        if ($success && ! empty($body['trackId'])) {

            // ذخیره‌ی trackId روی همین تراکنش تا Callback بعداً
            // بتواند با findByAuthority پیدایش کند.
            $transaction->update([
                'authority' => (string) $body['trackId'],
            ]);
        }

        return [

            'success' => $success,

            'track_id' => $body['trackId'] ?? null,

            // آدرسی که کاربر باید برای پرداخت به آن هدایت شود؛
            // توجه: این مسیر جدا از baseUrl (که برای API است) است.
            'payment_url' => $success
                ? 'https://gateway.zibal.ir/start/'.$body['trackId']
                : null,

            'message' => $body['message'] ?? null,

            'gateway_response' => $body,

        ];

    }

    /**
     * تایید پرداخت
     */
    public function verifyPayment(
        PaymentTransaction $transaction,
        array $data
    ): array {

        $response = Http::post(

            "{$this->baseUrl}/verify",

            [

                'merchant' => $this->merchant,

                // trackId از خودِ تراکنش خوانده می‌شود (نه از
                // ورودی کاربر) تا کسی نتواند با ارسال یه trackId
                // جعلی، تراکنش دیگری را تایید کند.
                'trackId' => $transaction->authority,

            ]

        );

        $body = $response->json();

        // ۱۰۰ یعنی همین الان تایید شد؛ ۲۰۱ یعنی قبلاً هم تایید
        // شده بود (که آن هم یعنی پرداخت واقعاً انجام شده).
        $success = in_array($body['result'] ?? null, [100, 201], true);

        return [

            'success' => $success,

            'reference_id' => $body['refNumber'] ?? null,

            'transaction_id' => (string) ($body['trackId'] ?? $transaction->authority),

            'card_pan' => $body['cardNumber'] ?? null,

            'message' => $body['message'] ?? null,

            'gateway_response' => $body,

        ];

    }

    /**
     * بازگشت وجه
     */
    public function refundPayment(
        PaymentTransaction $transaction
    ): array {

        /*
        |--------------------------------------------------------------------------
        | در حال حاضر API بازگشت وجه زیبال استفاده نشده است.
        |--------------------------------------------------------------------------
        */

        return [

            'success' => false,

            'message' => 'Refund is not implemented.',

        ];

    }
}
