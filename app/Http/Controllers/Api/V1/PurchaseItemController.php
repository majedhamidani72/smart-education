<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PurchaseItem;
use App\Helpers\ApiResponse;
use App\Services\PurchaseItemService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseItemResource;
use App\Http\Requests\PurchaseItem\StorePurchaseItemRequest;
use App\Http\Requests\PurchaseItem\UpdatePurchaseItemRequest;

class PurchaseItemController extends Controller
{
    /**
     * سرویس آیتم خرید
     */
    protected PurchaseItemService $service;

    public function __construct(
        PurchaseItemService $service
    ) {
        $this->service = $service;
    }

    /**
     * لیست آیتم‌های خرید
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            PurchaseItem::class
        );

        $purchaseItems = $this->service->paginate();

        return ApiResponse::success(

            PurchaseItemResource::collection(

                $purchaseItems

            ),

            'Purchase items retrieved successfully.'

        );
    }

    /**
     * نمایش یک آیتم خرید
     */
    public function show(
        PurchaseItem $purchaseItem
    )
    {
        $this->authorize(
            'view',
            $purchaseItem
        );

        return ApiResponse::success(

            new PurchaseItemResource(

                $purchaseItem

            ),

            'Purchase item retrieved successfully.'

        );
    }

    /**
     * ثبت آیتم خرید
     */
    public function store(
        StorePurchaseItemRequest $request
    )
    {
        $this->authorize(
            'create',
            PurchaseItem::class
        );

        $purchaseItem = $this->service->create(

            $request->validated()

        );

        return ApiResponse::success(

            new PurchaseItemResource(

                $purchaseItem

            ),

            'Purchase item created successfully.',

            201

        );
    }

    /**
     * بروزرسانی آیتم خرید
     */
    public function update(
        UpdatePurchaseItemRequest $request,
        PurchaseItem $purchaseItem
    )
    {
        $this->authorize(
            'update',
            $purchaseItem
        );

        $purchaseItem = $this->service->update(

            $purchaseItem,

            $request->validated()

        );

        return ApiResponse::success(

            new PurchaseItemResource(

                $purchaseItem

            ),

            'Purchase item updated successfully.'

        );
    }

    /**
     * حذف آیتم خرید
     */
    public function destroy(
        PurchaseItem $purchaseItem
    )
    {
        $this->authorize(
            'delete',
            $purchaseItem
        );

        $this->service->delete(

            $purchaseItem

        );

        return ApiResponse::success(

            null,

            'Purchase item deleted successfully.'

        );
    }
}
