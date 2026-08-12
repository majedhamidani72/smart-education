<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\StepByStepPage;
use App\Helpers\ApiResponse;
use App\Services\StepByStepPageService;
use App\Http\Controllers\Controller;
use App\Http\Resources\StepByStepPageResource;
use App\Http\Requests\StepByStepPage\StoreStepByStepPageRequest;
use App\Http\Requests\StepByStepPage\UpdateStepByStepPageRequest;

class StepByStepPageController extends Controller
{
    /**
     * سرویس صفحات گام به گام
     */
    protected StepByStepPageService $stepByStepPageService;

    public function __construct(
        StepByStepPageService $stepByStepPageService
    ) {
        $this->stepByStepPageService = $stepByStepPageService;
    }

    /**
     * لیست صفحات
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            StepByStepPage::class
        );

        $pages = $this->stepByStepPageService->paginate();

        return ApiResponse::success(

            StepByStepPageResource::collection(

                $pages

            ),

            'Step by step pages retrieved successfully.'

        );
    }

    /**
     * نمایش یک صفحه
     */
    public function show(
        StepByStepPage $stepByStepPage
    )
    {
        $this->authorize(
            'view',
            $stepByStepPage
        );

        return ApiResponse::success(

            new StepByStepPageResource(

                $stepByStepPage

            ),

            'Step by step page retrieved successfully.'

        );
    }

    /**
     * ایجاد صفحه
     */
    public function store(
        StoreStepByStepPageRequest $request
    )
    {
        $this->authorize(
            'create',
            StepByStepPage::class
        );

        $page = $this->stepByStepPageService->create(

            $request->validated(),

            $request->file('image')

        );

        return ApiResponse::success(

            new StepByStepPageResource(

                $page

            ),

            'Step by step page created successfully.',

            201

        );
    }

    /**
     * بروزرسانی صفحه
     */
    public function update(
        UpdateStepByStepPageRequest $request,
        StepByStepPage $stepByStepPage
    )
    {
        $this->authorize(
            'update',
            $stepByStepPage
        );

        $page = $this->stepByStepPageService->update(

            $stepByStepPage,

            $request->validated(),

            $request->file('image')

        );

        return ApiResponse::success(

            new StepByStepPageResource(

                $page

            ),

            'Step by step page updated successfully.'

        );
    }

    /**
     * حذف صفحه
     */
    public function destroy(
        StepByStepPage $stepByStepPage
    )
    {
        $this->authorize(
            'delete',
            $stepByStepPage
        );

        $this->stepByStepPageService->delete(

            $stepByStepPage

        );

        return ApiResponse::success(

            null,

            'Step by step page deleted successfully.'

        );
    }
}
