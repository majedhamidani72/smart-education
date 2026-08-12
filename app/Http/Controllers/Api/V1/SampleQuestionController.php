<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SampleQuestion;
use App\Helpers\ApiResponse;
use App\Services\SampleQuestionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\SampleQuestionResource;
use App\Http\Requests\SampleQuestion\StoreSampleQuestionRequest;
use App\Http\Requests\SampleQuestion\UpdateSampleQuestionRequest;

class SampleQuestionController extends Controller
{
    /**
     * سرویس نمونه سوال
     */
    protected SampleQuestionService $sampleQuestionService;

    public function __construct(
        SampleQuestionService $sampleQuestionService
    ) {
        $this->sampleQuestionService = $sampleQuestionService;
    }

    /**
     * لیست نمونه سوالات
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            SampleQuestion::class
        );

        $sampleQuestions = $this->sampleQuestionService->paginate();

        return ApiResponse::success(

            SampleQuestionResource::collection(

                $sampleQuestions

            ),

            'Sample questions retrieved successfully.'

        );
    }

    /**
     * نمایش یک نمونه سوال
     */
    public function show(
        SampleQuestion $sampleQuestion
    )
    {
        $this->authorize(
            'view',
            $sampleQuestion
        );

        return ApiResponse::success(

            new SampleQuestionResource(

                $sampleQuestion

            ),

            'Sample question retrieved successfully.'

        );
    }

    /**
     * ایجاد نمونه سوال
     */
    public function store(
        StoreSampleQuestionRequest $request
    )
    {
        $this->authorize(
            'create',
            SampleQuestion::class
        );

        $sampleQuestion = $this->sampleQuestionService->create(

            $request->validated(),

            $request->file('pdf')

        );

        return ApiResponse::success(

            new SampleQuestionResource(

                $sampleQuestion

            ),

            'Sample question created successfully.',

            201

        );
    }

    /**
     * بروزرسانی نمونه سوال
     */
    public function update(
        UpdateSampleQuestionRequest $request,
        SampleQuestion $sampleQuestion
    )
    {
        $this->authorize(
            'update',
            $sampleQuestion
        );

        $sampleQuestion = $this->sampleQuestionService->update(

            $sampleQuestion,

            $request->validated(),

            $request->file('pdf')

        );

        return ApiResponse::success(

            new SampleQuestionResource(

                $sampleQuestion

            ),

            'Sample question updated successfully.'

        );
    }

    /**
     * حذف نمونه سوال
     */
    public function destroy(
        SampleQuestion $sampleQuestion
    )
    {
        $this->authorize(
            'delete',
            $sampleQuestion
        );

        $this->sampleQuestionService->delete(

            $sampleQuestion

        );

        return ApiResponse::success(

            null,

            'Sample question deleted successfully.'

        );
    }
}
