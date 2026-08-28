<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PaymentTransaction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;

class PaymentController extends Controller
{
    /**
     * سرویس پرداخت
     */
    protected PaymentService $paymentService;

    public function __construct(
        PaymentService $paymentService
    ) {
        $this->paymentService = $paymentService;
    }

    /**
     * ایجاد تراکنش و دریافت لینک پرداخت
     */
    public function requestPayment(
        Request $request,
        Purchase $purchase
    )
    {
        $this->authorize(
            'view',
            $purchase
        );

        $validated = $request->validate([
            'return_to' => ['required', 'string', 'max:2048', 'regex:/^\/(?!\/)/'],
        ]);

        $transaction = $this->paymentService
            ->createTransaction(
                $purchase,
                $validated['return_to']
            );

        $result = $this->paymentService
            ->requestPayment(
                $transaction
            );

        if (! ($result['success'] ?? false) || empty($result['payment_url'])) {
            return ApiResponse::error(
                $result['message'] ?? 'درگاه زیبال لینک پرداخت معتبری برنگرداند.',
                $result['gateway_response'] ?? null,
                502
            );
        }

        return ApiResponse::success(

            $result,

            'لینک پرداخت با موفقیت ایجاد شد.'

        );
    }

    public function mode()
    {
        return ApiResponse::success([
            'mode' => config('services.zibal.merchant') === 'zibal' ? 'test' : 'real',
        ]);
    }

    public function completeTest(Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        return ApiResponse::success(
            $this->paymentService->completeTestPayment($purchase),
            'پرداخت آزمایشی دستی با موفقیت ثبت شد.'
        );
    }

    public function failTest(Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        return ApiResponse::success(
            $this->paymentService->failTestPayment($purchase),
            'پرداخت آزمایشی ناموفق ثبت شد.'
        );
    }

    /**
     * تایید پرداخت
     */
    public function verifyPayment(
        Request $request,
        PaymentTransaction $transaction
    )
    {
        $result = $this->paymentService
            ->verifyPayment(

                $transaction,

                $request->all()

            );

        return ApiResponse::success(

            $result,

            'وضعیت پرداخت بررسی شد.'

        );
    }

    /**
     * بازگشت وجه
     */
    public function refund(
        PaymentTransaction $transaction
    )
    {
        $this->authorize(
            'update',
            $transaction
        );

        $result = $this->paymentService
            ->refund(
                $transaction
            );

        return ApiResponse::success(

            $result,

            'درخواست بازگشت وجه ثبت شد.'

        );
    }
}
