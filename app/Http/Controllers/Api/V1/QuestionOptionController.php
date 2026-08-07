<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionOption\StoreQuestionOptionRequest;
use App\Http\Requests\QuestionOption\UpdateQuestionOptionRequest;
use App\Http\Resources\QuestionOptionResource;
use App\Models\QuestionOption;
use App\Services\QuestionOptionService;

class QuestionOptionController extends Controller
{
    protected QuestionOptionService $questionOptionService;

    public function __construct(
        QuestionOptionService $questionOptionService
    ) {
        $this->questionOptionService = $questionOptionService;
    }

    // لیست گزینه‌ها
    public function index()
    {
        return ApiResponse::success(
            QuestionOptionResource::collection(
                $this->questionOptionService->getAll()
            ),
            'Question options retrieved successfully.'
        );
    }

    // نمایش یک گزینه
    public function show(
        QuestionOption $questionOption
    ) {
        return ApiResponse::success(
            new QuestionOptionResource(
                $questionOption
            ),
            'Question option retrieved successfully.'
        );
    }

    // ایجاد گزینه
    public function store(
        StoreQuestionOptionRequest $request
    ) {
        $questionOption = $this->questionOptionService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new QuestionOptionResource(
                $questionOption
            ),
            'Question option created successfully.',
            201
        );
    }

    // بروزرسانی گزینه
    public function update(
        UpdateQuestionOptionRequest $request,
        QuestionOption $questionOption
    ) {
        $questionOption = $this->questionOptionService->update(
            $questionOption,
            $request->validated()
        );

        return ApiResponse::success(
            new QuestionOptionResource(
                $questionOption
            ),
            'Question option updated successfully.'
        );
    }

    // حذف گزینه
    public function destroy(
        QuestionOption $questionOption
    ) {
        $this->questionOptionService->delete(
            $questionOption
        );

        return ApiResponse::success(
            null,
            'Question option deleted successfully.'
        );
    }
}
