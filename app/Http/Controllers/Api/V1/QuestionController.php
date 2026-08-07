<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Services\QuestionService;

class QuestionController extends Controller
{
    protected QuestionService $questionService;

    public function __construct(
        QuestionService $questionService
    ) {
        $this->questionService = $questionService;
    }

    // لیست سوالات
    public function index()
    {
        return ApiResponse::success(
            QuestionResource::collection(
                $this->questionService->getAll()
            ),
            'Questions retrieved successfully.'
        );
    }

    // نمایش سوال
    public function show(
        Question $question
    ) {
        return ApiResponse::success(
            new QuestionResource($question),
            'Question retrieved successfully.'
        );
    }

    // ایجاد سوال
    public function store(
        StoreQuestionRequest $request
    ) {
        $question = $this->questionService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new QuestionResource($question),
            'Question created successfully.',
            201
        );
    }

    // بروزرسانی سوال
    public function update(
        UpdateQuestionRequest $request,
        Question $question
    ) {
        $question = $this->questionService->update(
            $question,
            $request->validated()
        );

        return ApiResponse::success(
            new QuestionResource($question),
            'Question updated successfully.'
        );
    }

    // حذف سوال
    public function destroy(
        Question $question
    ) {
        $this->questionService->delete($question);

        return ApiResponse::success(
            null,
            'Question deleted successfully.'
        );
    }
}
