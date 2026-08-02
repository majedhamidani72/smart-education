<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\GradeService;
use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Helpers\ApiResponse;
use App\Http\Requests\Grade\StoreGradeRequest;
use App\Http\Requests\Grade\UpdateGradeRequest;
use App\Models\Grade;



class GradeController extends Controller
{
    protected GradeService $gradeService;

    public function __construct(
        GradeService $gradeService
    ) {
        $this->gradeService = $gradeService;
    }

    public function index()
    {
        $grades = $this->gradeService->getAll();

        return ApiResponse::success(
            GradeResource::collection($grades),
            'Grades retrieved successfully.'
        );
    }



    public function show(int $id)
    {
        $grade = $this->gradeService->findById($id);

        return ApiResponse::success(
            new GradeResource($grade),
            'Grade retrieved successfully.'
        );
    }

    public function store(StoreGradeRequest $request)
    {
        $grade = $this->gradeService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new GradeResource($grade),
            'Grade created successfully.',
            201
        );
    }



    public function update(
        UpdateGradeRequest $request,
        Grade $grade // گرید را براساس ایدی ورودی از دیتابیس میگیرد.
    ) {
        $grade = $this->gradeService->update(

            $grade,

            $request->validated()

        );

        return ApiResponse::success(

            new GradeResource($grade),

            'Grade updated successfully.'

        );
    }


    public function destroy(Grade $grade)
    {
        $this->gradeService->delete($grade);

        return ApiResponse::success(
            null,
            'Grade deleted successfully.'
        );
    }
}
