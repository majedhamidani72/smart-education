<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Grade;
use App\Helpers\ApiResponse;
use App\Services\GradeService;
use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Http\Requests\Grade\StoreGradeRequest;
use App\Http\Requests\Grade\UpdateGradeRequest;

class GradeController extends Controller
{
    /**
     * Grade Service
     */
    protected GradeService $gradeService;

    /**
     * Constructor
     */
    public function __construct(
        GradeService $gradeService
    ) {
        $this->gradeService = $gradeService;
    }

    /**
     * لیست پایه‌ها
     */
    public function index()
    {
        // مرور پایه‌ها آزاد است — نیازی به مجوز مدیریتی نیست.
        //
        // نکته‌ی مهم: برخلاف کتاب/محتوا (که واقعاً می‌تواند زیاد
        // باشد)، تعداد پایه‌ها همیشه محدود و کوچک است (حداکثر ۱۲
        // تا) — صفحه‌بندی‌کردنش باعث می‌شد با هر داده‌ی آزمایشی
        // اضافه، بعضی پایه‌ها (مثلاً هشتم به بعد) از صفحه‌ی اول
        // بیرون بیفتند و اصلاً روی سایت دیده نشوند.
        $grades = \App\Models\Grade::orderBy('grade_number')->get();

        return ApiResponse::success(
            GradeResource::collection(
                $grades
            ),
            'Grades retrieved successfully.'
        );
    }

    /**
     * نمایش یک پایه
     */
    public function show(
        Grade $grade
    )
    {
        // مرور پایه‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        return ApiResponse::success(
            new GradeResource(
                $grade
            ),
            'Grade retrieved successfully.'
        );
    }

    /**
     * ایجاد پایه
     */
    public function store(
        StoreGradeRequest $request
    )
    {
        $this->authorize(
            'create',
            Grade::class
        );

        $grade = $this->gradeService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new GradeResource(
                $grade
            ),
            'Grade created successfully.',
            201
        );
    }

    /**
     * بروزرسانی پایه
     */
    public function update(
        UpdateGradeRequest $request,
        Grade $grade
    )
    {
        $this->authorize(
            'update',
            $grade
        );

        $grade = $this->gradeService->update(
            $grade,
            $request->validated()
        );

        return ApiResponse::success(
            new GradeResource(
                $grade
            ),
            'Grade updated successfully.'
        );
    }

    /**
     * حذف پایه
     */
    public function destroy(
        Grade $grade
    )
    {
        $this->authorize(
            'delete',
            $grade
        );

        $this->gradeService->delete(
            $grade
        );

        return ApiResponse::success(
            null,
            'Grade deleted successfully.'
        );
    }
}
