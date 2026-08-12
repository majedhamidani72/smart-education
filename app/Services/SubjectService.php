<?php

namespace App\Services;

use Throwable;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * صفحه‌بندی درس‌ها
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->subjectRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک درس
     */
    public function findById(
        int $id
    ): ?Subject
    {
        return $this->subjectRepository->findById(
            $id
        );
    }

    /**
     * ایجاد درس جدید
     */
    public function create(
        array $data
    ): Subject
    {
        try {

            return $this->subjectRepository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Subject creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * بروزرسانی درس
     */
    public function update(
        Subject $subject,
        array $data
    ): Subject
    {
        try {

            return $this->subjectRepository->update(

                $subject,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Subject update failed.', [

                'subject_id' => $subject->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * حذف درس
     */
    public function delete(
        Subject $subject
    ): bool
    {
        try {

            return $this->subjectRepository->delete(
                $subject
            );

        } catch (Throwable $e) {

            Log::error('Subject delete failed.', [

                'subject_id' => $subject->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
