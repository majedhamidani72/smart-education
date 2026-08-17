<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ProcessVideoJob;
use App\Models\Video;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class VideoService
{
    public function __construct(
        protected VideoRepositoryInterface $videoRepository,
        protected FileUploadService $fileUploadService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Read
    |--------------------------------------------------------------------------
    */

    public function getAll(): Collection
    {
        return $this->videoRepository->getAll();
    }

    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator {

        return $this->videoRepository->paginate(
            $perPage
        );
    }

    public function findById(
        int $id
    ): ?Video {

        return $this->videoRepository->findById(
            $id
        );
    }

    public function pending(): Collection
    {
        return $this->videoRepository->whereStatus(
            'pending'
        );
    }

    public function approved(): Collection
    {
        return $this->videoRepository->whereStatus(
            'approved'
        );
    }

    public function rejected(): Collection
    {
        return $this->videoRepository->whereStatus(
            'rejected'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data,
        UploadedFile $videoFile
    ): Video {

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload Video
            |--------------------------------------------------------------------------
            */

            $fileInfo = $this->fileUploadService->upload(

                $videoFile,

                'videos'

            );

            $data = array_merge(

                $data,

                $fileInfo

            );

            /*
            |--------------------------------------------------------------------------
            | Current User
            |--------------------------------------------------------------------------
            */

            $userId = Auth::id();

            if (! $userId) {

                throw new RuntimeException(
                    'Authenticated user not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Default Values
            |--------------------------------------------------------------------------
            */

            $data['uploaded_by'] = $userId;

            $data['duration'] = null;

            $data['quality'] = null;

            $data['thumbnail_path'] = null;

            $data['views_count'] = 0;

            $data['processing_status'] = 'pending';

            $data['download_allowed'] ??= false;

            /*
            |--------------------------------------------------------------------------
            | Create Video
            |--------------------------------------------------------------------------
            */

            $video = $this->videoRepository->create(
                $data
            );

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Queue Processing
            |--------------------------------------------------------------------------
            */

            ProcessVideoJob::dispatch(
                $video->id
            )->afterCommit();

            return $video->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'Video creation failed.',

                [

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Video $video,
        array $data,
        ?UploadedFile $videoFile = null
    ): Video {

        DB::beginTransaction();

        try {

            if ($videoFile instanceof UploadedFile) {

                $fileInfo = $this->fileUploadService->upload(

                    $videoFile,

                    'videos'

                );

                $this->fileUploadService->delete(

                    $video->directory,

                    $video->filename

                );

                $data = array_merge(

                    $data,

                    $fileInfo

                );

                $data['processing_status'] = 'pending';

                $data['duration'] = null;

                $data['quality'] = null;

                $data['thumbnail_path'] = null;

                $data['approved_by'] = null;

                $data['approved_at'] = null;

                $data['rejected_reason'] = null;
            }

            $video = $this->videoRepository->update(

                $video,

                $data

            );

            DB::commit();

            if ($videoFile instanceof UploadedFile) {

                ProcessVideoJob::dispatch(
                    $video->id
                )->afterCommit();
            }

            return $video->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'Video update failed.',

                [

                    'video_id' => $video->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        Video $video
    ): Video {

        DB::beginTransaction();

        try {

            $video = $this->videoRepository->update(

                $video,

                [

                    'processing_status' => 'approved',

                    'approved_by' => Auth::id(),

                    'approved_at' => now(),

                    'rejected_reason' => null,

                ]

            );

            DB::commit();

            return $video->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'Video approval failed.',

                [

                    'video_id' => $video->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        Video $video,
        string $reason
    ): Video {

        DB::beginTransaction();

        try {

            $video = $this->videoRepository->update(

                $video,

                [

                    'processing_status' => 'rejected',

                    'approved_by' => Auth::id(),

                    'approved_at' => now(),

                    'rejected_reason' => $reason,

                ]

            );

            DB::commit();

            return $video->fresh([

                'uploader',

                'approver',

                'contentItem.section.chapter.book',

            ]);
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'Video rejection failed.',

                [

                    'video_id' => $video->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        Video $video
    ): bool {

        DB::beginTransaction();

        try {

            $deleted = $this->videoRepository->delete(
                $video
            );

            if ($deleted) {

                $this->fileUploadService->delete(

                    $video->directory,

                    $video->filename

                );
            }

            DB::commit();

            return $deleted;
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(

                'Video delete failed.',

                [

                    'video_id' => $video->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }
}
