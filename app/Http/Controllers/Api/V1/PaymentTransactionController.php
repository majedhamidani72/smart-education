<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PaymentTransaction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\PaymentTransactionService;
use App\Http\Resources\PaymentTransactionResource;
use App\Http\Requests\PaymentTransaction\StorePaymentTransactionRequest;
use App\Http\Requests\PaymentTransaction\UpdatePaymentTransactionRequest;

class PaymentTransactionController extends Controller
{
    /**
     * Service
     */
    protected PaymentTransactionService $service;

    public function __construct(
        PaymentTransactionService $service
    ) {
        $this->service = $service;
    }

    /**
     * لیست تراکنش‌ها
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            PaymentTransaction::class
        );

        $transactions = $this->service->paginate();

        return ApiResponse::success(

            PaymentTransactionResource::collection(

                $transactions

            ),

            'Transactions retrieved successfully.'

        );
    }

    /**
     * نمایش تراکنش
     */
    public function show(
        PaymentTransaction $paymentTransaction
    )
    {
        $this->authorize(
            'view',
            $paymentTransaction
        );

        return ApiResponse::success(

            new PaymentTransactionResource(

                $paymentTransaction

            ),

            'Transaction retrieved successfully.'

        );
    }

    /**
     * ایجاد تراکنش
     */
    public function store(
        StorePaymentTransactionRequest $request
    )
    {
        $this->authorize(
            'create',
            PaymentTransaction::class
        );

        $transaction = $this->service->create(

            $request->validated()

        );

        return ApiResponse::success(

            new PaymentTransactionResource(

                $transaction

            ),

            'Transaction created successfully.',

            201

        );
    }

    /**
     * بروزرسانی تراکنش
     */
    public function update(
        UpdatePaymentTransactionRequest $request,
        PaymentTransaction $paymentTransaction
    )
    {
        $this->authorize(
            'update',
            $paymentTransaction
        );

        $paymentTransaction = $this->service->update(

            $paymentTransaction,

            $request->validated()

        );

        return ApiResponse::success(

            new PaymentTransactionResource(

                $paymentTransaction

            ),

            'Transaction updated successfully.'

        );
    }

    /**
     * حذف تراکنش
     */
    public function destroy(
        PaymentTransaction $paymentTransaction
    )
    {
        $this->authorize(
            'delete',
            $paymentTransaction
        );

        $this->service->delete(

            $paymentTransaction

        );

        return ApiResponse::success(

            null,

            'Transaction deleted successfully.'

        );
    }

    /**
     * ثبت پرداخت موفق
     */
    public function markAsPaid(
        PaymentTransaction $paymentTransaction
    )
    {
        $this->authorize(
            'update',
            $paymentTransaction
        );

        $this->service->markAsPaid(

            $paymentTransaction,

            request()->all()

        );

        return ApiResponse::success(

            new PaymentTransactionResource(

                $paymentTransaction->fresh()

            ),

            'Transaction marked as paid.'

        );
    }

    /**
     * ثبت پرداخت ناموفق
     */
    public function markAsFailed(
        PaymentTransaction $paymentTransaction
    )
    {
        $this->authorize(
            'update',
            $paymentTransaction
        );

        $this->service->markAsFailed(

            $paymentTransaction,

            request('message')

        );

        return ApiResponse::success(

            new PaymentTransactionResource(

                $paymentTransaction->fresh()

            ),

            'Transaction marked as failed.'

        );
    }

    /**
     * ثبت بازگشت وجه
     */
    public function markAsRefunded(
        PaymentTransaction $paymentTransaction
    )
    {
        $this->authorize(
            'update',
            $paymentTransaction
        );

        $this->service->markAsRefunded(

            $paymentTransaction

        );

        return ApiResponse::success(

            new PaymentTransactionResource(

                $paymentTransaction->fresh()

            ),

            'Transaction refunded successfully.'

        );
    }
}
