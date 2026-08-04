<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Video\StoreVideoRequest;
use App\Http\Requests\Video\UpdateVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Services\VideoService;

class VideoController extends Controller
{
    // سرویس ویدئو
    protected VideoService $videoService;

    public function __construct(
        VideoService $videoService
    ) {
        $this->videoService = $videoService;
    }

    // لیست ویدئوها
    public function index()
    {
        return ApiResponse::success(
            VideoResource::collection(
                $this->videoService->getAll()
            ),
            'Videos retrieved successfully.'
        );
    }

    // نمایش یک ویدئو
    public function show(Video $video)
    {
        return ApiResponse::success(
            new VideoResource($video),
            'Video retrieved successfully.'
        );
    }

    // ایجاد ویدئو
    public function store(StoreVideoRequest $request)
    {
        $video = $this->videoService->create(
            $request->validated(),
            $request->file('video')
        );

        return ApiResponse::success(
            new VideoResource($video),
            'Video created successfully.',
            201
        );
    }

    // بروزرسانی ویدئو
    public function update(
        UpdateVideoRequest $request,
        Video $video
    ) {
        $video = $this->videoService->update(
            $video,
            $request->validated(),
            $request->file('video')
        );

        return ApiResponse::success(
            new VideoResource($video),
            'Video updated successfully.'
        );
    }

    // حذف ویدئو
    public function destroy(Video $video)
    {
        $this->videoService->delete($video);

        return ApiResponse::success(
            null,
            'Video deleted successfully.'
        );
    }
}
