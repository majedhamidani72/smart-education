<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Subscription;
use App\Helpers\ApiResponse;
use App\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionRequest;

class SubscriptionController extends Controller
{
    /**
     * سرویس اشتراک
     */
    protected SubscriptionService $subscriptionService;

    /**
     * Constructor
     */
    public function __construct(
        SubscriptionService $subscriptionService
    ) {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * لیست اشتراک‌ها
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            Subscription::class
        );

        $subscriptions = $this->subscriptionService->paginate();

        return ApiResponse::success(

            SubscriptionResource::collection(

                $subscriptions

            ),

            'Subscriptions retrieved successfully.'

        );
    }

    /**
     * نمایش یک اشتراک
     */
    public function show(
        Subscription $subscription
    )
    {
        $this->authorize(
            'view',
            $subscription
        );

        return ApiResponse::success(

            new SubscriptionResource(

                $subscription

            ),

            'Subscription retrieved successfully.'

        );
    }

    /**
     * ایجاد اشتراک
     */
    public function store(
        StoreSubscriptionRequest $request
    )
    {
        $this->authorize(
            'create',
            Subscription::class
        );

        $subscription = $this->subscriptionService->create(

            $request->validated()

        );

        return ApiResponse::success(

            new SubscriptionResource(

                $subscription

            ),

            'Subscription created successfully.',

            201

        );
    }

    /**
     * بروزرسانی اشتراک
     */
    public function update(
        UpdateSubscriptionRequest $request,
        Subscription $subscription
    )
    {
        $this->authorize(
            'update',
            $subscription
        );

        $subscription = $this->subscriptionService->update(

            $subscription,

            $request->validated()

        );

        return ApiResponse::success(

            new SubscriptionResource(

                $subscription

            ),

            'Subscription updated successfully.'

        );
    }

    /**
     * حذف اشتراک
     */
    public function destroy(
        Subscription $subscription
    )
    {
        $this->authorize(
            'delete',
            $subscription
        );

        $this->subscriptionService->delete(

            $subscription

        );

        return ApiResponse::success(

            null,

            'Subscription deleted successfully.'

        );
    }

    /**
     * اشتراک‌های فعال
     */
    public function active()
    {
        $this->authorize(
            'viewAny',
            Subscription::class
        );

        return ApiResponse::success(

            SubscriptionResource::collection(

                $this->subscriptionService->getActive()

            ),

            'Active subscriptions retrieved successfully.'

        );
    }

    /**
     * اشتراک‌های منقضی شده
     */
    public function expired()
    {
        $this->authorize(
            'viewAny',
            Subscription::class
        );

        return ApiResponse::success(

            SubscriptionResource::collection(

                $this->subscriptionService->getExpired()

            ),

            'Expired subscriptions retrieved successfully.'

        );
    }

    /**
     * اشتراک‌های لغو شده
     */
    public function cancelled()
    {
        $this->authorize(
            'viewAny',
            Subscription::class
        );

        return ApiResponse::success(

            SubscriptionResource::collection(

                $this->subscriptionService->getCancelled()

            ),

            'Cancelled subscriptions retrieved successfully.'

        );
    }

    /**
     * فعال کردن اشتراک
     */
    public function activate(
        Subscription $subscription
    )
    {
        $this->authorize(
            'update',
            $subscription
        );

        $subscription = $this->subscriptionService->activate(

            $subscription

        );

        return ApiResponse::success(

            new SubscriptionResource(

                $subscription

            ),

            'Subscription activated successfully.'

        );
    }

    /**
     * لغو اشتراک
     */
    public function cancel(
        Subscription $subscription
    )
    {
        $this->authorize(
            'update',
            $subscription
        );

        $subscription = $this->subscriptionService->cancel(

            $subscription

        );

        return ApiResponse::success(

            new SubscriptionResource(

                $subscription

            ),

            'Subscription cancelled successfully.'

        );
    }

    /**
     * تمدید اشتراک
     */
    public function extend(
        Subscription $subscription
    )
    {
        $this->authorize(
            'update',
            $subscription
        );

        $days = (int) request(
            'days'
        );

        $subscription = $this->subscriptionService->extend(

            $subscription,

            $days

        );

        return ApiResponse::success(

            new SubscriptionResource(

                $subscription

            ),

            'Subscription extended successfully.'

        );
    }
}
