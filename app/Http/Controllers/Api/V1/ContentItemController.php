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
        return ApiResponse::success(

            ContentItemResource::collection(

                $this->contentItemService->getAll()

            ),

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
        return ApiResponse::success(

            new ContentItemResource(
                $contentItem
            ),

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
        $contentItem = $this->contentItemService->create(

            $request->validated()

        );

        return ApiResponse::success(

            new ContentItemResource(
                $contentItem
            ),

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
        $contentItem = $this->contentItemService->update(

            $contentItem,

            $request->validated()

        );

        return ApiResponse::success(

            new ContentItemResource(
                $contentItem
            ),

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
        $contentItem = $this->contentItemService
            ->submitForReview($contentItem);

        return ApiResponse::success(

            new ContentItemResource(
                $contentItem
            ),

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
        $contentItem = $this->contentItemService
            ->approve($contentItem);

        return ApiResponse::success(

            new ContentItemResource(
                $contentItem
            ),

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
        $contentItem = $this->contentItemService
            ->reject($contentItem);

        return ApiResponse::success(

            new ContentItemResource(
                $contentItem
            ),

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
        $contentItem = $this->contentItemService
            ->publish($contentItem);

        return ApiResponse::success(

            new ContentItemResource(
                $contentItem
            ),

            'Content published successfully.'

        );
    }
}
