<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Http\Resources\GradeResource;

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
     * دریافت یک پایه
     */
    public function findById(int $id): ?Grade
    {
        return $this->gradeRepository->findById($id);
    }

    /**
     * ایجاد پایه
     */
    public function create(array $data): Grade
    {
        return $this->gradeRepository->create($data);
    }

    /**
     * بروزرسانی پایه
     */
    public function update(
        Grade $grade,
        array $data
    ): Grade {
        return $this->gradeRepository->update(
            $grade,
            $data
        );
    }

    /**
     * حذف پایه
     */
    public function delete(
        Grade $grade
    ): bool {
        return $this->gradeRepository->delete(
            $grade
        );
    }
}
