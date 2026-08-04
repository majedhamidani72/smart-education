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
    protected ChapterService $chapterService;

    public function __construct(ChapterService $chapterService)
    {
        $this->chapterService = $chapterService;
    }

    public function index()
    {
        return ApiResponse::success(
            ChapterResource::collection(
                $this->chapterService->getAll()
            ),
            'Chapters retrieved successfully.'
        );
    }

    public function show(Chapter $chapter)
    {
        return ApiResponse::success(
            new ChapterResource($chapter),
            'Chapter retrieved successfully.'
        );
    }

    public function store(StoreChapterRequest $request)
    {
        $chapter = $this->chapterService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new ChapterResource($chapter),
            'Chapter created successfully.',
            201
        );
    }

    public function update(UpdateChapterRequest $request, Chapter $chapter)
    {
        $chapter = $this->chapterService->update(
            $chapter,
            $request->validated()
        );

        return ApiResponse::success(
            new ChapterResource($chapter),
            'Chapter updated successfully.'
        );
    }

    public function destroy(Chapter $chapter)
    {
        $this->chapterService->delete($chapter);

        return ApiResponse::success(
            null,
            'Chapter deleted successfully.'
        );
    }
}
