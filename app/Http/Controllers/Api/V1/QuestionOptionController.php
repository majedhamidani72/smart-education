<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\QuestionOption;
use App\Helpers\ApiResponse;
use App\Services\QuestionOptionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionOptionResource;
use App\Http\Requests\QuestionOption\StoreQuestionOptionRequest;
use App\Http\Requests\QuestionOption\UpdateQuestionOptionRequest;

class QuestionOptionController extends Controller
{
    protected QuestionOptionService $questionOptionService;


    public function __construct(
        QuestionOptionService $questionOptionService
    ) {
        $this->questionOptionService = $questionOptionService;
    }


    /**
     * لیست گزینه‌ها
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            QuestionOption::class
        );


        $options = $this->questionOptionService->paginate();


        return ApiResponse::success(
            QuestionOptionResource::collection($options),
            'Question options retrieved successfully.'
        );
    }


    /**
     * نمایش یک گزینه
     */
    public function show(
        QuestionOption $questionOption
    )
    {
        $this->authorize(
            'view',
            $questionOption
        );


        $questionOption->load([
            'question',
        ]);


        return ApiResponse::success(
            new QuestionOptionResource($questionOption),
            'Question option retrieved successfully.'
        );
    }


    /**
     * ایجاد گزینه
     */
    public function store(
        StoreQuestionOptionRequest $request
    )
    {
        $this->authorize(
            'create',
            QuestionOption::class
        );


        $questionOption = $this->questionOptionService->create(
            $request->validated()
        );


        return ApiResponse::success(
            new QuestionOptionResource($questionOption),
            'Question option created successfully.',
            201
        );
    }


    /**
     * بروزرسانی گزینه
     */
    public function update(
        UpdateQuestionOptionRequest $request,
        QuestionOption $questionOption
    )
    {
        $this->authorize(
            'update',
            $questionOption
        );


        $questionOption = $this->questionOptionService->update(
            $questionOption,
            $request->validated()
        );


        return ApiResponse::success(
            new QuestionOptionResource($questionOption),
            'Question option updated successfully.'
        );
    }


    /**
     * حذف گزینه
     */
    public function destroy(
        QuestionOption $questionOption
    )
    {
        $this->authorize(
            'delete',
            $questionOption
        );


        $this->questionOptionService->delete(
            $questionOption
        );


        return ApiResponse::success(
            null,
            'Question option deleted successfully.'
        );
    }
}
