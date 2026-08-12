<?php

namespace App\Services;

use Throwable;
use App\Models\Chapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\ChapterRepositoryInterface;

class ChapterService
{
    /**
     * Repository مربوط به فصل‌ها
     */
    protected ChapterRepositoryInterface $chapterRepository;

    public function __construct(
        ChapterRepositoryInterface $chapterRepository
    ) {
        $this->chapterRepository = $chapterRepository;
    }

    /**
     * دریافت همه فصل‌ها
     */
    public function getAll(): Collection
    {
        return $this->chapterRepository->getAll();
    }

    /**
     * صفحه‌بندی فصل‌ها
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->chapterRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک فصل
     */
    public function findById(
        int $id
    ): ?Chapter
    {
        return $this->chapterRepository->findById(
            $id
        );
    }

    /**
     * ایجاد فصل
     */
    public function create(
        array $data
    ): Chapter
    {
        try {

            return $this->chapterRepository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Chapter creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * بروزرسانی فصل
     */
    public function update(
        Chapter $chapter,
        array $data
    ): Chapter
    {
        try {

            return $this->chapterRepository->update(

                $chapter,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Chapter update failed.', [

                'chapter_id' => $chapter->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * حذف فصل
     */
    public function delete(
        Chapter $chapter
    ): bool
    {
        try {

            return $this->chapterRepository->delete(
                $chapter
            );

        } catch (Throwable $e) {

            Log::error('Chapter delete failed.', [

                'chapter_id' => $chapter->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }
}
