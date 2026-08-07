<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use App\Services\PurchaseItemService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseItemResource;
use App\Models\PurchaseItem;
use App\Http\Requests\PurchaseItem\StorePurchaseItemRequest;
use App\Http\Requests\PurchaseItem\UpdatePurchaseItemRequest;

class PurchaseItemController extends Controller
{
    protected PurchaseItemService $service;

    public function __construct(
        PurchaseItemService $service
    ) {
        $this->service = $service;
    }

    /**
     * لیست آیتم‌ها
     */
    public function index(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'لیست آیتم‌های خرید.',

            'data' => PurchaseItemResource::collection(

                $this->service->getAll()

            ),

        ]);
    }

    /**
     * نمایش یک آیتم
     */
    public function show(
        PurchaseItem $purchaseItem
    ): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'اطلاعات آیتم خرید.',

            'data' => new PurchaseItemResource(

                $purchaseItem

            ),

        ]);
    }

    /**
     * ثبت آیتم
     */
    public function store(
        StorePurchaseItemRequest $request
    ): JsonResponse
    {
        $purchaseItem = $this->service->create(

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'آیتم خرید با موفقیت ثبت شد.',

            'data' => new PurchaseItemResource(

                $purchaseItem

            ),

        ], 201);
    }

    /**
     * بروزرسانی آیتم
     */
    public function update(
        UpdatePurchaseItemRequest $request,
        PurchaseItem $purchaseItem
    ): JsonResponse
    {
        $purchaseItem = $this->service->update(

            $purchaseItem,

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'آیتم خرید بروزرسانی شد.',

            'data' => new PurchaseItemResource(

                $purchaseItem

            ),

        ]);
    }

    /**
     * حذف آیتم
     */
    public function destroy(
        PurchaseItem $purchaseItem
    ): JsonResponse
    {
        $this->service->delete(

            $purchaseItem

        );

        return response()->json([

            'success' => true,

            'message' => 'آیتم خرید حذف شد.',

        ]);
    }
}
