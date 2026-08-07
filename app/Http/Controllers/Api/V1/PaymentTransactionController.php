<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\PaymentTransactionService;
use App\Http\Resources\PaymentTransactionResource;
use App\Http\Requests\PaymentTransaction\StorePaymentTransactionRequest;
use App\Http\Requests\PaymentTransaction\UpdatePaymentTransactionRequest;

class PaymentTransactionController extends Controller
{
    protected PaymentTransactionService $service;

    public function __construct(
        PaymentTransactionService $service
    ) {
        $this->service = $service;
    }

    /**
     * لیست تراکنش‌ها
     */
    public function index(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'لیست تراکنش‌ها.',

            'data' => PaymentTransactionResource::collection(

                $this->service->getUserTransactions(
                    auth()->id()
                )

            ),

        ]);
    }

    /**
     * نمایش تراکنش
     */
    public function show(
        PaymentTransaction $paymentTransaction
    ): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'اطلاعات تراکنش.',

            'data' => new PaymentTransactionResource(

                $paymentTransaction

            ),

        ]);
    }

    /**
     * ثبت تراکنش
     */
    public function store(
        StorePaymentTransactionRequest $request
    ): JsonResponse
    {
        $transaction = $this->service->create(

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'تراکنش با موفقیت ثبت شد.',

            'data' => new PaymentTransactionResource(

                $transaction

            ),

        ], 201);
    }

    /**
     * بروزرسانی تراکنش
     */
    public function update(
        UpdatePaymentTransactionRequest $request,
        PaymentTransaction $paymentTransaction
    ): JsonResponse
    {
        $this->service->update(

            $paymentTransaction,

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'تراکنش بروزرسانی شد.',

            'data' => new PaymentTransactionResource(

                $paymentTransaction->fresh()

            ),

        ]);
    }

    /**
     * حذف تراکنش
     */
    public function destroy(
        PaymentTransaction $paymentTransaction
    ): JsonResponse
    {
        $this->service->delete(

            $paymentTransaction

        );

        return response()->json([

            'success' => true,

            'message' => 'تراکنش حذف شد.',

        ]);
    }

    /**
     * پرداخت موفق
     */
    public function markAsPaid(
        PaymentTransaction $paymentTransaction
    ): JsonResponse
    {
        $this->service->markAsPaid(

            $paymentTransaction,

            request()->all()

        );

        return response()->json([

            'success' => true,

            'message' => 'تراکنش پرداخت شد.',

            'data' => new PaymentTransactionResource(

                $paymentTransaction->fresh()

            ),

        ]);
    }

    /**
     * پرداخت ناموفق
     */
    public function markAsFailed(
        PaymentTransaction $paymentTransaction
    ): JsonResponse
    {
        $this->service->markAsFailed(

            $paymentTransaction,

            request('message')

        );

        return response()->json([

            'success' => true,

            'message' => 'تراکنش ناموفق ثبت شد.',

            'data' => new PaymentTransactionResource(

                $paymentTransaction->fresh()

            ),

        ]);
    }

    /**
     * بازگشت وجه
     */
    public function markAsRefunded(
        PaymentTransaction $paymentTransaction
    ): JsonResponse
    {
        $this->service->markAsRefunded(

            $paymentTransaction

        );

        return response()->json([

            'success' => true,

            'message' => 'بازگشت وجه ثبت شد.',

            'data' => new PaymentTransactionResource(

                $paymentTransaction->fresh()

            ),

        ]);
    }
}
