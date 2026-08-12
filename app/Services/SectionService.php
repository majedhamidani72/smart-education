<?php

namespace App\Services;

use Throwable;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\SectionRepositoryInterface;

class SectionService
{
    /**
     * Repository بخش‌ها
     */
    protected SectionRepositoryInterface $sectionRepository;

    public function __construct(
        SectionRepositoryInterface $sectionRepository
    ) {
        $this->sectionRepository = $sectionRepository;
    }

    /**
     * دریافت همه بخش‌ها
     */
    public function getAll(): Collection
    {
        return $this->sectionRepository->getAll();
    }

    /**
     * صفحه‌بندی بخش‌ها
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->sectionRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک بخش
     */
    public function findById(
        int $id
    ): ?Section
    {
        return $this->sectionRepository->findById(
            $id
        );
    }

    /**
     * ایجاد بخش
     */
    public function create(
        array $data
    ): Section
    {
        try {

            return $this->sectionRepository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Section creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * بروزرسانی بخش
     */
    public function update(
        Section $section,
        array $data
    ): Section
    {
        try {

            return $this->sectionRepository->update(

                $section,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Section update failed.', [

                'section_id' => $section->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف بخش
     */
    public function delete(
        Section $section
    ): bool
    {
        try {

            return $this->sectionRepository->delete(
                $section
            );

        } catch (Throwable $e) {

            Log::error('Section delete failed.', [

                'section_id' => $section->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
