<?php

namespace App\Services\Payment\Providers;

use Illuminate\Support\Facades\Http;
use App\Models\PaymentTransaction;
use App\Services\Payment\Contracts\PaymentGatewayInterface;

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

                'amount' => $transaction->amount,

                'callbackUrl' => $this->callbackUrl,

                'orderId' => $transaction->id,

                'description' => 'Purchase #' . $transaction->purchase_id,

            ]

        );

        return $response->json();

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

                'trackId' => $data['trackId'],

            ]

        );

        return $response->json();

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
