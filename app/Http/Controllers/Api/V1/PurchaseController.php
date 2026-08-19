<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Purchase;
use App\Helpers\ApiResponse;
use App\Services\PurchaseService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseResource;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;

class PurchaseController extends Controller
{
    /**
     * سرویس خرید
     */
    protected PurchaseService $service;

    public function __construct(
        PurchaseService $service
    ) {
        $this->service = $service;
    }

    /**
     * لیست خریدها
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            Purchase::class
        );

        $purchases = $this->service->paginate();

        return ApiResponse::success(

            PurchaseResource::collection(

                $purchases

            ),

            'Purchases retrieved successfully.'

        );
    }

    /**
     * نمایش خرید
     */
    public function show(
        Purchase $purchase
    )
    {
        $this->authorize(
            'view',
            $purchase
        );

        return ApiResponse::success(

            new PurchaseResource(

                $purchase

            ),

            'Purchase retrieved successfully.'

        );
    }

    /**
     * ثبت خرید
     */
    public function store(
        StorePurchaseRequest $request
    )
    {
        $this->authorize(
            'create',
            Purchase::class
        );

        $validated = $request->validated();

        $purchase = $this->service->createFromPlans(

            // user_id همیشه از روی کاربر واقعاً واردشده تعیین
            // می‌شود، نه از ورودی کلاینت — تا کسی نتواند برای
            // کاربر دیگری خرید ثبت کند.
            auth()->id(),

            $validated['plan_ids'],

            $validated['notes'] ?? null

        );

        return ApiResponse::success(

            new PurchaseResource(

                $purchase

            ),

            'Purchase created successfully.',

            201

        );
    }

    /**
     * ویرایش خرید
     */
    public function update(
        UpdatePurchaseRequest $request,
        Purchase $purchase
    )
    {
        $this->authorize(
            'update',
            $purchase
        );

        $purchase = $this->service->update(

            $purchase,

            $request->validated()

        );

        return ApiResponse::success(

            new PurchaseResource(

                $purchase

            ),

            'Purchase updated successfully.'

        );
    }

    /**
     * حذف خرید
     */
    public function destroy(
        Purchase $purchase
    )
    {
        $this->authorize(
            'delete',
            $purchase
        );

        $this->service->delete(

            $purchase

        );

        return ApiResponse::success(

            null,

            'Purchase deleted successfully.'

        );
    }

    /**
     * خریدهای پرداخت شده
     */
    public function paid()
    {
        $this->authorize(
            'viewAny',
            Purchase::class
        );

        return ApiResponse::success(

            PurchaseResource::collection(

                $this->service->paid()

            ),

            'Paid purchases retrieved successfully.'

        );
    }

    /**
     * خریدهای در انتظار
     */
    public function pending()
    {
        $this->authorize(
            'viewAny',
            Purchase::class
        );

        return ApiResponse::success(

            PurchaseResource::collection(

                $this->service->pending()

            ),

            'Pending purchases retrieved successfully.'

        );
    }
}
