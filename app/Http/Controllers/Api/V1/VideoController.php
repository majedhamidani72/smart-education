<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Video\RejectVideoRequest;
use App\Http\Requests\Video\StoreVideoRequest;
use App\Http\Requests\Video\UpdateVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\JsonResponse;

class VideoController extends Controller
{
    public function __construct(
        protected VideoService $videoService
    ) {
    }


    /**
     * لیست ویدئوها
     */
    public function index(): JsonResponse
    {
        $this->authorize(
            'viewAny',
            Video::class
        );


        $videos = $this->videoService->paginate();


        $videos->getCollection()->load([
            'uploader',
            'approver',
            'contentItem',
        ]);


        return ApiResponse::success(
            VideoResource::collection($videos),
            __('api.videos_retrieved')
        );
    }


    /**
     * نمایش یک ویدئو
     */
    public function show(
        Video $video
    ): JsonResponse
    {
        $this->authorize(
            'view',
            $video
        );


        $video->load([
            'uploader',
            'approver',
            'contentItem',
        ]);


        return ApiResponse::success(
            new VideoResource($video),
            __('api.video_retrieved')
        );
    }


    /**
     * ایجاد ویدئو
     */
    public function store(
        StoreVideoRequest $request
    ): JsonResponse
    {
        $this->authorize(
            'create',
            Video::class
        );


        $video = $this->videoService->create(
            $request->validated(),
            $request->file('video')
        );


        $video->load([
            'uploader',
            'contentItem',
        ]);


        return ApiResponse::success(
            new VideoResource($video),
            __('api.video_created'),
            201
        );
    }


    /**
     * بروزرسانی ویدئو
     */
    public function update(
        UpdateVideoRequest $request,
        Video $video
    ): JsonResponse
    {
        $this->authorize(
            'update',
            $video
        );


        $video = $this->videoService->update(
            $video,
            $request->validated(),
            $request->file('video')
        );


        $video->load([
            'uploader',
            'approver',
            'contentItem',
        ]);


        return ApiResponse::success(
            new VideoResource($video),
            __('api.video_updated')
        );
    }


    /**
     * حذف ویدئو
     */
    public function destroy(
        Video $video
    ): JsonResponse
    {
        $this->authorize(
            'delete',
            $video
        );


        $this->videoService->delete(
            $video
        );


        return ApiResponse::success(
            null,
            __('api.video_deleted')
        );
    }



    /**
     * ویدئوهای در انتظار بررسی
     */
    public function pending(): JsonResponse
    {
        $this->authorize(
            'viewAny',
            Video::class
        );


        $videos = $this->videoService->pending();


        $videos->load([
            'uploader',
            'contentItem',
        ]);


        return ApiResponse::success(
            VideoResource::collection($videos),
            __('api.pending_videos_retrieved')
        );
    }



    /**
     * ویدئوهای تایید شده
     */
    public function approved(): JsonResponse
    {
        $this->authorize(
            'viewAny',
            Video::class
        );


        $videos = $this->videoService->approved();


        $videos->load([
            'uploader',
            'approver',
            'contentItem',
        ]);


        return ApiResponse::success(
            VideoResource::collection($videos),
            __('api.approved_videos_retrieved')
        );
    }



    /**
     * ویدئوهای رد شده
     */
    public function rejected(): JsonResponse
    {
        $this->authorize(
            'viewAny',
            Video::class
        );


        $videos = $this->videoService->rejected();


        $videos->load([
            'uploader',
            'approver',
            'contentItem',
        ]);


        return ApiResponse::success(
            VideoResource::collection($videos),
            __('api.rejected_videos_retrieved')
        );
    }



    /**
     * تایید ویدئو
     */
    public function approve(
        Video $video
    ): JsonResponse
    {
        $this->authorize(
            'approve',
            $video
        );


        $video = $this->videoService->approve(
            $video
        );


        $video->load([
            'uploader',
            'approver',
            'contentItem',
        ]);


        return ApiResponse::success(
            new VideoResource($video),
            __('api.video_approved')
        );
    }



    /**
     * رد ویدئو
     */
    public function reject(
        RejectVideoRequest $request,
        Video $video
    ): JsonResponse
    {
        $this->authorize(
            'reject',
            $video
        );


        $video = $this->videoService->reject(
            $video,
            $request->validated()['rejected_reason']
        );


        $video->load([
            'uploader',
            'approver',
            'contentItem',
        ]);


        return ApiResponse::success(
            new VideoResource($video),
            __('api.video_rejected')
        );
    }
}
