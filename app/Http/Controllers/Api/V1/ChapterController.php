<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Chapter;
use App\Helpers\ApiResponse;
use App\Services\ChapterService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChapterResource;
use App\Http\Requests\Chapter\StoreChapterRequest;
use App\Http\Requests\Chapter\UpdateChapterRequest;

class ChapterController extends Controller
{
    /**
     * Service
     */
    protected ChapterService $chapterService;

    /**
     * Constructor
     */
    public function __construct(
        ChapterService $chapterService
    ) {
        $this->chapterService = $chapterService;
    }

    /**
     * لیست فصل‌ها
     */
    public function index()
    {
        // مرور فصل‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        $chapters = $this->chapterService->paginate();

        return ApiResponse::success(
            ChapterResource::collection(
                $chapters
            ),
            'Chapters retrieved successfully.'
        );
    }

    /**
     * نمایش یک فصل
     */
    public function show(
        Chapter $chapter
    )
    {
        // مرور فصل‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        return ApiResponse::success(
            new ChapterResource(
                $chapter
            ),
            'Chapter retrieved successfully.'
        );
    }

    /**
     * ایجاد فصل
     */
    public function store(
        StoreChapterRequest $request
    )
    {
        $this->authorize(
            'create',
            Chapter::class
        );

        $chapter = $this->chapterService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new ChapterResource(
                $chapter
            ),
            'Chapter created successfully.',
            201
        );
    }

    /**
     * بروزرسانی فصل
     */
    public function update(
        UpdateChapterRequest $request,
        Chapter $chapter
    )
    {
        $this->authorize(
            'update',
            $chapter
        );

        $chapter = $this->chapterService->update(
            $chapter,
            $request->validated()
        );

        return ApiResponse::success(
            new ChapterResource(
                $chapter
            ),
            'Chapter updated successfully.'
        );
    }

    /**
     * حذف نرم فصل
     */
    public function destroy(
        Chapter $chapter
    )
    {
        $this->authorize(
            'delete',
            $chapter
        );

        $this->chapterService->delete(
            $chapter
        );

        return ApiResponse::success(
            null,
            'Chapter deleted successfully.'
        );
    }
}
