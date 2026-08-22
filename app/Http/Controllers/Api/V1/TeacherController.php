<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Grade;
use App\Models\User;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\BookResource;
use App\Models\TeacherAssignment;
use App\Models\Book;
use Illuminate\Http\Request;

/**
 * معلم‌ها — برای مسیر انتخاب دانش‌آموز
 * --------------------------------------------------------------------
 * دو حالت اصلی:
 * ۱) پایه‌های ۱ تا ۶: دانش‌آموز اول معلم را انتخاب می‌کند، بعد
 *    کتاب‌های همان معلم را می‌بیند (forGrade + books).
 * ۲) پایه‌های ۷ تا ۱۲: دانش‌آموز اول کتاب را انتخاب می‌کند؛ اگر آن
 *    کتاب چند معلم داشت، از بین آن‌ها یکی را انتخاب می‌کند
 *    (BookController::teachers).
 */
class TeacherController extends Controller
{
    /**
     * معلم‌هایی که حداقل یک کتاب فعال برای این پایه تدریس می‌کنند
     * — بدون تکرار.
     */
    public function forGrade(Grade $grade)
    {
        $teacherIds = TeacherAssignment::query()
            ->where('is_active', true)
            ->whereHas('book', function ($query) use ($grade) {

                $query->where('is_active', true)
                    ->whereHas('appGradeSubject', fn($q) => $q->where('grade_id', $grade->id));
            })
            ->distinct()
            ->pluck('teacher_id');

        $teachers = User::query()
            ->whereIn('id', $teacherIds)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            TeacherResource::collection($teachers)
        );
    }

    /**
     * کتاب‌های یک معلم مشخص — اختیاری فیلتر بر اساس پایه (برای
     * حالتی که از مسیر «انتخاب پایه → انتخاب معلم» آمده‌ایم).
     */
    public function books(User $teacher, Request $request)
    {
        $bookIds = TeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('book_id');

        $books = Book::query()
            ->whereIn('id', $bookIds)
            ->where('is_active', true)
            ->when($request->query('grade_id'), function ($query, $gradeId) {

                $query->whereHas('appGradeSubject', fn($q) => $q->where('grade_id', $gradeId));
            })
            ->orderBy('sort_order')
            ->get();

        return ApiResponse::success(
            BookResource::collection($books)
        );
    }
}
