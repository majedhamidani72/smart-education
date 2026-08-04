<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\SubjectRepositoryInterface;

class SubjectService
{
    /**
     * Repository مربوط به درس‌ها
     */
    protected SubjectRepositoryInterface $subjectRepository;

    /**
     * تزریق Repository
     */
    public function __construct(
        SubjectRepositoryInterface $subjectRepository
    ) {
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * دریافت تمام درس‌ها
     */
    public function getAll(): Collection
    {
        return $this->subjectRepository->getAll();
    }

    /**
     * دریافت یک درس
     */
    public function findById(int $id): ?Subject
    {
        return $this->subjectRepository->findById($id);
    }

    /**
     * ایجاد درس جدید
     */
    public function create(array $data): Subject
    {
        return $this->subjectRepository->create($data);
    }

    /**
     * بروزرسانی درس
     */
    public function update(
        Subject $subject,
        array $data
    ): Subject {

        return $this->subjectRepository->update(
            $subject,
            $data
        );
    }

    /**
     * حذف نرم درس
     */
    public function delete(
        Subject $subject
    ): bool {

        return $this->subjectRepository->delete(
            $subject
        );
    }
}
