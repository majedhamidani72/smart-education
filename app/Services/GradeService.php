<?php

namespace App\Services;

use Throwable;
use App\Models\Grade;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\GradeRepositoryInterface;

class GradeService
{
    /**
     * Repository مربوط به پایه‌ها
     */
    protected GradeRepositoryInterface $gradeRepository;

    /**
     * Constructor
     */
    public function __construct(
        GradeRepositoryInterface $gradeRepository
    ) {
        $this->gradeRepository = $gradeRepository;
    }

    /**
     * دریافت تمام پایه‌ها
     */
    public function getAll(): Collection
    {
        return $this->gradeRepository->getAll();
    }

    /**
     * صفحه‌بندی پایه‌ها
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->gradeRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک پایه
     */
    public function findById(
        int $id
    ): ?Grade
    {
        return $this->gradeRepository->findById(
            $id
        );
    }

    /**
     * ایجاد پایه
     */
    public function create(
        array $data
    ): Grade
    {
        try {

            return $this->gradeRepository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Grade creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * بروزرسانی پایه
     */
    public function update(
        Grade $grade,
        array $data
    ): Grade
    {
        try {

            return $this->gradeRepository->update(

                $grade,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Grade update failed.', [

                'grade_id' => $grade->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * حذف پایه
     */
    public function delete(
        Grade $grade
    ): bool
    {
        try {

            return $this->gradeRepository->delete(
                $grade
            );

        } catch (Throwable $e) {

            Log::error('Grade delete failed.', [

                'grade_id' => $grade->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }
}
