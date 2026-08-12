<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ProcessVideoJob;
use App\Models\Video;
use Throwable;
use RuntimeException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\VideoRepositoryInterface;

class VideoService
{
    public function __construct(
        protected VideoRepositoryInterface $videoRepository,
        protected FileUploadService $fileUploadService
    ) {
    }


    public function getAll(): Collection
    {
        return $this->videoRepository->getAll();
    }


    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->videoRepository->paginate($perPage);
    }


    public function findById(
        int $id
    ): ?Video
    {
        return $this->videoRepository->findById($id);
    }


    public function pending(): Collection
    {
        return $this->videoRepository->whereStatus('pending');
    }


    public function approved(): Collection
    {
        return $this->videoRepository->whereStatus('approved');
    }


    public function rejected(): Collection
    {
        return $this->videoRepository->whereStatus('rejected');
    }



    public function create(
        array $data,
        UploadedFile $videoFile
    ): Video
    {
        DB::beginTransaction();

        try {

            $fileInfo = $this->fileUploadService->storeFromPath(
                $videoFile,
                'videos'
            );


            $data = array_merge(
                $data,
                $fileInfo
            );


            $userId = Auth::id();


            if (! $userId) {

                throw new RuntimeException(
                    'Authenticated user not found.'
                );

            }


            $data['uploaded_by'] = $userId;
            $data['duration'] = null;
            $data['quality'] = null;
            $data['thumbnail_path'] = null;
            $data['views_count'] = 0;
            $data['processing_status'] = 'pending';
            $data['download_allowed'] ??= false;


            $video = $this->videoRepository->create($data);


            DB::commit();


            ProcessVideoJob::dispatch(
                $video->id
            )->afterCommit();


            return $video->fresh([
                'uploader',
                'contentItem',
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



    public function update(
        Video $video,
        array $data,
        ?UploadedFile $videoFile
    ): Video
    {
        DB::beginTransaction();

        try {


            if ($videoFile) {


                $fileInfo = $this->fileUploadService->replaceFromPath(
                    $videoFile,
                    $video->directory,
                    $video->filename,
                    'videos'
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



            if ($videoFile) {

                ProcessVideoJob::dispatch(
                    $video->id
                )->afterCommit();

            }



            return $video->fresh([
                'uploader',
                'approver',
                'contentItem',
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



    public function approve(
        Video $video
    ): Video
    {
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



    public function reject(
        Video $video,
        string $reason
    ): Video
    {
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



    public function delete(
        Video $video
    ): bool
    {
        DB::beginTransaction();

        try {


            $deleted = $this->videoRepository->delete($video);


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
