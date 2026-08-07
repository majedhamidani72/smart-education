<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StepByStepPage\StoreStepByStepPageRequest;
use App\Http\Requests\StepByStepPage\UpdateStepByStepPageRequest;
use App\Http\Resources\StepByStepPageResource;
use App\Models\StepByStepPage;
use App\Services\StepByStepPageService;

class StepByStepPageController extends Controller
{
    protected StepByStepPageService $stepByStepPageService;

    public function __construct(
        StepByStepPageService $stepByStepPageService
    ) {
        $this->stepByStepPageService = $stepByStepPageService;
    }

    // لیست صفحات
    public function index()
    {
        return ApiResponse::success(
            StepByStepPageResource::collection(
                $this->stepByStepPageService->getAll()
            ),
            'Step by step pages retrieved successfully.'
        );
    }

    // نمایش یک صفحه
    public function show(
        StepByStepPage $stepByStepPage
    ) {
        return ApiResponse::success(
            new StepByStepPageResource(
                $stepByStepPage
            ),
            'Step by step page retrieved successfully.'
        );
    }

    // ایجاد صفحه
    public function store(
        StoreStepByStepPageRequest $request
    ) {
        $page = $this->stepByStepPageService->create(
            $request->validated(),
            $request->file('image')
        );

        return ApiResponse::success(
            new StepByStepPageResource($page),
            'Step by step page created successfully.',
            201
        );
    }

    // بروزرسانی صفحه
    public function update(
        UpdateStepByStepPageRequest $request,
        StepByStepPage $stepByStepPage
    ) {
        $page = $this->stepByStepPageService->update(
            $stepByStepPage,
            $request->validated(),
            $request->file('image')
        );

        return ApiResponse::success(
            new StepByStepPageResource($page),
            'Step by step page updated successfully.'
        );
    }

    // حذف صفحه
    public function destroy(
        StepByStepPage $stepByStepPage
    ) {
        $this->stepByStepPageService->delete(
            $stepByStepPage
        );

        return ApiResponse::success(
            null,
            'Step by step page deleted successfully.'
        );
    }
}
