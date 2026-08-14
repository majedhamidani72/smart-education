<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\TeacherAssignment;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\TeacherAssignmentRepositoryInterface;


class TeacherAssignmentRepository extends BaseRepository implements TeacherAssignmentRepositoryInterface
{

    public function __construct(
        TeacherAssignment $teacherAssignment
    ) {
        parent::__construct(
            $teacherAssignment
        );
    }



    /**
     * دریافت دسترسی‌های یک معلم
     */
    public function getByTeacher(
        int $teacherId
    ): Collection
    {
        return $this->model
            ->where(
                'teacher_id',
                $teacherId
            )
            ->where(
                'is_active',
                true
            )
            ->with([
                'book.gradeSubject.grade',
                'book.gradeSubject.subject',
            ])
            ->latest()
            ->get();
    }



    /**
     * دریافت دسترسی‌های یک کتاب
     */
    public function getByBook(
        int $bookId
    ): Collection
    {
        return $this->model
            ->where(
                'book_id',
                $bookId
            )
            ->where(
                'is_active',
                true
            )
            ->with([
                'teacher',
            ])
            ->latest()
            ->get();
    }

}
