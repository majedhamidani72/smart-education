<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use App\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionRequest;

class SubscriptionController extends Controller
{
    protected SubscriptionService $service;

    public function __construct(
        SubscriptionService $service
    ) {
        $this->service = $service;
    }

    /**
     * لیست اشتراک‌ها
     */
    public function index(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'لیست اشتراک‌ها.',

            'data' => SubscriptionResource::collection(

                $this->service->getAll()

            ),

        ]);
    }

    /**
     * نمایش اشتراک
     */
    public function show(
        Subscription $subscription
    ): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'اطلاعات اشتراک.',

            'data' => new SubscriptionResource(

                $subscription

            ),

        ]);
    }

    /**
     * ثبت اشتراک
     */
    public function store(
        StoreSubscriptionRequest $request
    ): JsonResponse
    {
        $subscription = $this->service->create(

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'اشتراک با موفقیت ثبت شد.',

            'data' => new SubscriptionResource(

                $subscription

            ),

        ], 201);
    }

    /**
     * بروزرسانی اشتراک
     */
    public function update(
        UpdateSubscriptionRequest $request,
        Subscription $subscription
    ): JsonResponse
    {
        $subscription = $this->service->update(

            $subscription,

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'اشتراک بروزرسانی شد.',

            'data' => new SubscriptionResource(

                $subscription

            ),

        ]);
    }

    /**
     * حذف اشتراک
     */
    public function destroy(
        Subscription $subscription
    ): JsonResponse
    {
        $this->service->delete(

            $subscription

        );

        return response()->json([

            'success' => true,

            'message' => 'اشتراک حذف شد.',

        ]);
    }

    /**
     * اشتراک‌های فعال
     */
    public function active(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'data' => SubscriptionResource::collection(

                $this->service->getActive()

            ),

        ]);
    }

    /**
     * اشتراک‌های منقضی
     */
    public function expired(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'data' => SubscriptionResource::collection(

                $this->service->getExpired()

            ),

        ]);
    }

    /**
     * اشتراک‌های لغو شده
     */
    public function cancelled(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'data' => SubscriptionResource::collection(

                $this->service->getCancelled()

            ),

        ]);
    }

    /**
     * فعال کردن اشتراک
     */
    public function activate(
        Subscription $subscription
    ): JsonResponse
    {
        $subscription = $this->service->activate(
            $subscription
        );

        return response()->json([

            'success' => true,

            'message' => 'اشتراک فعال شد.',

            'data' => new SubscriptionResource(
                $subscription
            ),

        ]);
    }

    /**
     * لغو اشتراک
     */
    public function cancel(
        Subscription $subscription
    ): JsonResponse
    {
        $subscription = $this->service->cancel(
            $subscription
        );

        return response()->json([

            'success' => true,

            'message' => 'اشتراک لغو شد.',

            'data' => new SubscriptionResource(
                $subscription
            ),

        ]);
    }

    /**
     * تمدید اشتراک
     */
    public function extend(
        Subscription $subscription
    ): JsonResponse
    {
        $days = (int) request()->input(
            'days',
            30
        );

        $subscription = $this->service->extend(

            $subscription,

            $days

        );

        return response()->json([

            'success' => true,

            'message' => 'اشتراک تمدید شد.',

            'data' => new SubscriptionResource(

                $subscription

            ),

        ]);
    }
}
