<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;

class PaymentCallbackController extends Controller
{
    /**
     * سرویس پرداخت
     */
    protected PaymentService $paymentService;

    /**
     * ریپازیتوری تراکنش
     */
    protected PaymentTransactionRepositoryInterface $transactionRepository;

    public function __construct(
        PaymentService $paymentService,
        PaymentTransactionRepositoryInterface $transactionRepository
    ) {

        $this->paymentService = $paymentService;

        $this->transactionRepository = $transactionRepository;

    }

    /**
     * Callback زیبال
     */
    public function __invoke(
        Request $request
    )
    {

        $authority = $request->input(
            'trackId'
        );

        $transaction = $this->transactionRepository
            ->findByAuthority(
                $authority
            );

        if (!$transaction) {

            return ApiResponse::notFound(
                'تراکنش یافت نشد.'
            );

        }

        $result = $this->paymentService
            ->verifyPayment(

                $transaction,

                $request->all()

            );

        return ApiResponse::success(

            $result,

            'پرداخت با موفقیت بررسی شد.'

        );

    }
}
