<?php

namespace App\Services;

use Throwable;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\VideoRepositoryInterface;

class VideoService
{
    // Repository مربوط به ویدئو
    protected VideoRepositoryInterface $videoRepository;

    // سرویس مدیریت فایل
    protected FileUploadService $fileUploadService;

    public function __construct(
        VideoRepositoryInterface $videoRepository,
        FileUploadService $fileUploadService
    ) {
        $this->videoRepository = $videoRepository;
        $this->fileUploadService = $fileUploadService;
    }

    // دریافت همه ویدئوها
    public function getAll(): Collection
    {
        return $this->videoRepository->getAll();
    }

    // دریافت یک ویدئو
    public function findById(int $id): ?Video
    {
        return $this->videoRepository->findById($id);
    }

    // ایجاد ویدئو
    public function create(
        array $data,
        ?UploadedFile $videoFile
    ): Video {

        DB::beginTransaction();

        try {

            if ($videoFile) {

                $fileInfo = $this->fileUploadService->upload(
                    $videoFile,
                    'videos'
                );

                $data['storage_disk'] = 'public';
                $data['file_path'] = $fileInfo['file_path'];
                $data['original_name'] = $fileInfo['original_name'];
                $data['mime_type'] = $fileInfo['mime_type'];
                $data['file_size'] = $fileInfo['file_size'];
            }

            $data['duration'] = null;
            $data['thumbnail_path'] = null;
            $data['views_count'] = 0;

            $video = $this->videoRepository->create($data);

            DB::commit();

            return $video;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(
                'Create Video Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    // بروزرسانی ویدئو
    public function update(
        Video $video,
        array $data,
        ?UploadedFile $videoFile
    ): Video {

        DB::beginTransaction();

        try {

            if ($videoFile) {

                $fileInfo = $this->fileUploadService->replace(
                    $videoFile,
                    $video->file_path,
                    'videos'
                );

                $data['storage_disk'] = 'public';
                $data['file_path'] = $fileInfo['file_path'];
                $data['original_name'] = $fileInfo['original_name'];
                $data['mime_type'] = $fileInfo['mime_type'];
                $data['file_size'] = $fileInfo['file_size'];
            }

            $video = $this->videoRepository->update(
                $video,
                $data
            );

            DB::commit();

            return $video;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(
                'Update Video Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    // حذف ویدئو
    public function delete(
        Video $video
    ): bool {

        DB::beginTransaction();

        try {

            $this->fileUploadService->delete(
                $video->file_path
            );

            $deleted = $this->videoRepository->delete(
                $video
            );

            DB::commit();

            return $deleted;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(
                'Delete Video Error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }
}
