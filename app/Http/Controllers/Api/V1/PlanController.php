<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Plan;
use App\Helpers\ApiResponse;
use App\Services\PlanService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Http\Requests\Plan\StorePlanRequest;
use App\Http\Requests\Plan\UpdatePlanRequest;

class PlanController extends Controller
{
    /**
     * سرویس پلن
     */
    protected PlanService $service;

    /**
     * Constructor
     */
    public function __construct(
        PlanService $service
    ) {
        $this->service = $service;
    }

    /**
     * لیست پلن‌ها
     */
    public function index()
    {
        // مرور پلن‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        $plans = $this->service->paginate();

        return ApiResponse::success(

            PlanResource::collection(

                $plans

            ),

            'Plans retrieved successfully.'

        );
    }

    /**
     * پلن‌های فعال
     */
    public function active()
    {
        // مرور پلن‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        return ApiResponse::success(

            PlanResource::collection(

                $this->service->getActive()

            ),

            'Active plans retrieved successfully.'

        );
    }

    /**
     * نمایش یک پلن
     */
    public function show(
        Plan $plan
    )
    {
        // مرور پلن‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        return ApiResponse::success(

            new PlanResource(

                $plan

            ),

            'Plan retrieved successfully.'

        );
    }

    /**
     * ایجاد پلن
     */
    public function store(
        StorePlanRequest $request
    )
    {
        $this->authorize(
            'create',
            Plan::class
        );

        $plan = $this->service->create(

            $request->validated()

        );

        return ApiResponse::success(

            new PlanResource(

                $plan

            ),

            'Plan created successfully.',

            201

        );
    }

    /**
     * بروزرسانی پلن
     */
    public function update(
        UpdatePlanRequest $request,
        Plan $plan
    )
    {
        $this->authorize(
            'update',
            $plan
        );

        $plan = $this->service->update(

            $plan,

            $request->validated()

        );

        return ApiResponse::success(

            new PlanResource(

                $plan

            ),

            'Plan updated successfully.'

        );
    }

    /**
     * حذف پلن
     */
    public function destroy(
        Plan $plan
    )
    {
        $this->authorize(
            'delete',
            $plan
        );

        $this->service->delete(

            $plan

        );

        return ApiResponse::success(

            null,

            'Plan deleted successfully.'

        );
    }
}
