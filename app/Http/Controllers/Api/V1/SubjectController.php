<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Services\SubjectService;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubjectResource;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * سرویس مربوط به درس‌ها
     */
    protected SubjectService $subjectService;

    /**
     * تزریق سرویس
     */
    public function __construct(
        SubjectService $subjectService
    ) {
        $this->subjectService = $subjectService;
    }

    /**
     * لیست درس‌ها
     */
    public function index()
    {
        $subjects = $this->subjectService->getAll();

        return ApiResponse::success(

            SubjectResource::collection($subjects),

            'Subjects retrieved successfully.'

        );
    }


    /**
     * ایجاد درس جدید
     */
    public function store(
        StoreSubjectRequest $request
    ) {
        $subject = $this->subjectService->create(

            $request->validated()

        );

        return ApiResponse::success(

            new SubjectResource($subject),

            'Subject created successfully.',

            201

        );
    }


    /**
     * نمایش اطلاعات یک درس
     */
    public function show(int $id)
    {
        $subject = $this->subjectService->findById($id);

        if (!$subject) {

            return ApiResponse::notFound(
                'Subject not found.'
            );
        }

        return ApiResponse::success(

            new SubjectResource($subject),

            'Subject retrieved successfully.'

        );
    }


    /**
     * بروزرسانی اطلاعات درس
     */
    public function update(
        UpdateSubjectRequest $request,
        Subject $subject
    ) {
        $subject = $this->subjectService->update(

            $subject,

            $request->validated()

        );

        return ApiResponse::success(

            new SubjectResource($subject),

            'Subject updated successfully.'

        );
    }




    /**
     * حذف نرم درس
     */
    public function destroy(
        Subject $subject
    ) {
        $this->subjectService->delete(
            $subject
        );

        return ApiResponse::success(

            null,

            'Subject deleted successfully.'

        );
    }
}
