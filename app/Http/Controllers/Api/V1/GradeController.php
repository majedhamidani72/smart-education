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
        $this->authorize(
            'viewAny',
            Grade::class
        );

        $grades = $this->gradeService->paginate();

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
        $this->authorize(
            'view',
            $grade
        );

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
