<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\TeacherAssignmentRepositoryInterface;


class TeacherAssignmentService
{

    protected TeacherAssignmentRepositoryInterface $teacherAssignmentRepository;


    public function __construct(
        TeacherAssignmentRepositoryInterface $teacherAssignmentRepository
    ) {
        $this->teacherAssignmentRepository = $teacherAssignmentRepository;
    }



    /**
     * دریافت همه دسترسی‌ها
     */
    public function getAll(): Collection
    {
        return $this->teacherAssignmentRepository->getAll();
    }



    /**
     * صفحه‌بندی دسترسی‌ها
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->teacherAssignmentRepository
            ->paginate($perPage);
    }



    /**
     * دریافت یک دسترسی
     */
    public function findById(
        int $id
    ): ?TeacherAssignment
    {
        return $this->teacherAssignmentRepository
            ->findById($id);
    }



    /**
     * دسترسی‌های یک معلم
     */
    public function getByTeacher(
        int $teacherId
    ): Collection
    {
        return $this->teacherAssignmentRepository
            ->getByTeacher($teacherId);
    }



    /**
     * دسترسی‌های یک کتاب
     */
    public function getByBook(
        int $bookId
    ): Collection
    {
        return $this->teacherAssignmentRepository
            ->getByBook($bookId);
    }



    /**
     * ایجاد دسترسی جدید
     */
    public function create(
        array $data
    ): TeacherAssignment
    {
        try {

            return $this->teacherAssignmentRepository
                ->create($data);


        } catch (Throwable $e) {


            Log::error(
                'Teacher assignment creation failed.',
                [
                    'data' => $data,
                    'error' => $e->getMessage(),
                ]
            );


            throw $e;

        }
    }



    /**
     * بروزرسانی دسترسی
     */
    public function update(
        TeacherAssignment $teacherAssignment,
        array $data
    ): TeacherAssignment
    {
        try {

            return $this->teacherAssignmentRepository
                ->update(
                    $teacherAssignment,
                    $data
                );


        } catch (Throwable $e) {


            Log::error(
                'Teacher assignment update failed.',
                [
                    'teacher_assignment_id' => $teacherAssignment->id,
                    'error' => $e->getMessage(),
                ]
            );


            throw $e;

        }
    }



    /**
     * حذف دسترسی
     */
    public function delete(
        TeacherAssignment $teacherAssignment
    ): bool
    {
        try {

            return $this->teacherAssignmentRepository
                ->delete(
                    $teacherAssignment
                );


        } catch (Throwable $e) {


            Log::error(
                'Teacher assignment delete failed.',
                [
                    'teacher_assignment_id' => $teacherAssignment->id,
                    'error' => $e->getMessage(),
                ]
            );


            throw $e;

        }
    }

}
