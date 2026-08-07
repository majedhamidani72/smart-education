<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    protected PurchaseService $service;

    public function __construct(
        PurchaseService $service
    ) {
        $this->service = $service;
    }

    /**
     * لیست خریدها
     */
    public function index(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'لیست خریدها.',

            'data' => PurchaseResource::collection(

                $this->service->getAll()

            ),

        ]);
    }

    /**
     * نمایش خرید
     */
    public function show(
        int $purchase
    ): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'اطلاعات خرید.',

            'data' => new PurchaseResource(

                $this->service->find($purchase)

            ),

        ]);
    }

    /**
     * ثبت خرید
     */
    public function store(
        StorePurchaseRequest $request
    ): JsonResponse
    {
        $purchase = $this->service->create(

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'خرید با موفقیت ثبت شد.',

            'data' => new PurchaseResource(

                $purchase

            ),

        ], 201);
    }

    /**
     * ویرایش خرید
     */
    public function update(
        UpdatePurchaseRequest $request,
        int $purchase
    ): JsonResponse
    {
        $purchase = $this->service->update(

            $purchase,

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'خرید بروزرسانی شد.',

            'data' => new PurchaseResource(

                $purchase

            ),

        ]);
    }

    /**
     * حذف خرید
     */
    public function destroy(
        int $purchase
    ): JsonResponse
    {
        $this->service->delete(

            $purchase

        );

        return response()->json([

            'success' => true,

            'message' => 'خرید حذف شد.',

        ]);
    }

    /**
     * خریدهای پرداخت شده
     */
    public function paid(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'data' => PurchaseResource::collection(

                $this->service->paid()

            ),

        ]);
    }

    /**
     * خریدهای در انتظار
     */
    public function pending(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'data' => PurchaseResource::collection(

                $this->service->pending()

            ),

        ]);
    }
}
