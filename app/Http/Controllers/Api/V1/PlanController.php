<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\StorePlanRequest;
use App\Http\Requests\Plan\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;

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
    public function index(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'لیست پلن‌ها.',

            'data' => PlanResource::collection(

                $this->service->getAll()

            ),

        ]);
    }

    /**
     * پلن‌های فعال
     */
    public function active(): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'پلن‌های فعال.',

            'data' => PlanResource::collection(

                $this->service->getActive()

            ),

        ]);
    }

    /**
     * نمایش یک پلن
     */
    public function show(
        int $id
    ): JsonResponse
    {
        return response()->json([

            'success' => true,

            'message' => 'اطلاعات پلن.',

            'data' => new PlanResource(

                $this->service->findById($id)

            ),

        ]);
    }

    /**
     * ایجاد پلن
     */
    public function store(
        StorePlanRequest $request
    ): JsonResponse
    {
        $plan = $this->service->create(

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'پلن با موفقیت ایجاد شد.',

            'data' => new PlanResource(

                $plan

            ),

        ], 201);
    }

    /**
     * بروزرسانی پلن
     */
    public function update(
        UpdatePlanRequest $request,
        int $id
    ): JsonResponse
    {
        $plan = $this->service->findById($id);

        $plan = $this->service->update(

            $plan,

            $request->validated()

        );

        return response()->json([

            'success' => true,

            'message' => 'پلن بروزرسانی شد.',

            'data' => new PlanResource(

                $plan

            ),

        ]);
    }

    /**
     * حذف پلن
     */
    public function destroy(
        int $id
    ): JsonResponse
    {
        $plan = $this->service->findById($id);

        $this->service->delete($plan);

        return response()->json([

            'success' => true,

            'message' => 'پلن حذف شد.',

        ]);
    }
}
