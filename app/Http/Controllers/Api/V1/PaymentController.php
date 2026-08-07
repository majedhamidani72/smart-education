<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PaymentTransaction;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

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
        Purchase $purchase
    )
    {

        $transaction = $this->paymentService
            ->createTransaction(
                $purchase
            );

        $result = $this->paymentService
            ->requestPayment(
                $transaction
            );

        return ApiResponse::success(

            $result,

            'لینک پرداخت با موفقیت ایجاد شد.'

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
