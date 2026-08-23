<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ContentItem;
use App\Helpers\ApiResponse;
use App\Services\ContentItemService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContentItemResource;
use App\Http\Requests\ContentItem\StoreContentItemRequest;
use App\Http\Requests\ContentItem\UpdateContentItemRequest;

class ContentItemController extends Controller
{
    protected ContentItemService $contentItemService;


    public function __construct(
        ContentItemService $contentItemService
    ) {
        $this->contentItemService = $contentItemService;
    }


    /**
     * لیست محتواها
     */
    public function index()
    {
        // مرور محتوا (حتی رایگان‌ها بدون ورود) آزاد است — منطق
        // دسترسی به فایل واقعی داخل خودِ ContentItemResource انجام
        // می‌شود.

        $contentItems = $this->contentItemService->paginate();


        return ApiResponse::success(
            ContentItemResource::collection($contentItems),
            'Content items retrieved successfully.'
        );
    }


    /**
     * نمایش یک محتوا
     */
    public function show(
        ContentItem $contentItem
    )
    {
        // مرور محتوا (حتی رایگان‌ها بدون ورود) آزاد است — منطق
        // دسترسی به فایل واقعی داخل خودِ ContentItemResource انجام
        // می‌شود.


        $contentItem->load([
            'creator',
            'reviewer',
            'contentType',
            'video',
            'stepByStep.pages',
            'pdfFile',
            'sampleQuestions',
        ]);


        return ApiResponse::success(
            new ContentItemResource($contentItem),
            'Content item retrieved successfully.'
        );
    }


    /**
     * ایجاد محتوا
     */
    public function store(
        StoreContentItemRequest $request
    )
    {
        $this->authorize(
            'create',
            ContentItem::class
        );


        $contentItem = $this->contentItemService->create(
            $request->validated()
        );


        $contentItem->load([
            'creator',
            'contentType',
            'video',
            'stepByStep.pages',
            'pdfFile',
            'sampleQuestions',
        ]);


        return ApiResponse::success(
            new ContentItemResource($contentItem),
            'Content item created successfully.',
            201
        );
    }


    /**
     * بروزرسانی محتوا
     */
    public function update(
        UpdateContentItemRequest $request,
        ContentItem $contentItem
    )
    {
        $this->authorize(
            'update',
            $contentItem
        );


        $contentItem = $this->contentItemService->update(
            $contentItem,
            $request->validated()
        );


        $contentItem->load([
            'creator',
            'reviewer',
            'contentType',
            'video',
            'stepByStep.pages',
            'pdfFile',
            'sampleQuestions',
        ]);


        return ApiResponse::success(
            new ContentItemResource($contentItem),
            'Content item updated successfully.'
        );
    }


    /**
     * حذف نرم محتوا
     */
    public function destroy(
        ContentItem $contentItem
    )
    {
        $this->authorize(
            'delete',
            $contentItem
        );


        $this->contentItemService->delete(
            $contentItem
        );


        return ApiResponse::success(
            null,
            'Content item deleted successfully.'
        );
    }


    /**
     * ارسال برای بررسی
     */
    public function submitForReview(
        ContentItem $contentItem
    )
    {
        $this->authorize(
            'update',
            $contentItem
        );


        $contentItem = $this->contentItemService->submitForReview(
            $contentItem
        );


        return ApiResponse::success(
            new ContentItemResource($contentItem),
            'Content submitted successfully.'
        );
    }


    /**
     * تایید محتوا
     */
    public function approve(
        ContentItem $contentItem
    )
    {
        $this->authorize(
            'approve',
            $contentItem
        );


        $contentItem = $this->contentItemService->approve(
            $contentItem
        );


        return ApiResponse::success(
            new ContentItemResource($contentItem),
            'Content approved successfully.'
        );
    }


    /**
     * رد محتوا
     */
    public function reject(
        ContentItem $contentItem
    )
    {
        $this->authorize(
            'reject',
            $contentItem
        );


        $contentItem = $this->contentItemService->reject(
            $contentItem
        );


        return ApiResponse::success(
            new ContentItemResource($contentItem),
            'Content rejected successfully.'
        );
    }


    /**
     * انتشار محتوا
     */
    public function publish(
        ContentItem $contentItem
    )
    {
        $this->authorize(
            'publish',
            $contentItem
        );


        $contentItem = $this->contentItemService->publish(
            $contentItem
        );


        return ApiResponse::success(
            new ContentItemResource($contentItem),
            'Content published successfully.'
        );
    }
}
