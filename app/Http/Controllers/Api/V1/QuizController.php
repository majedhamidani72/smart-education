<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\StoreQuizRequest;
use App\Http\Requests\Quiz\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use App\Services\QuizService;

class QuizController extends Controller
{
    protected QuizService $quizService;

    public function __construct(
        QuizService $quizService
    ) {
        $this->quizService = $quizService;
    }

    // لیست آزمون‌ها
    public function index()
    {
        return ApiResponse::success(
            QuizResource::collection(
                $this->quizService->getAll()
            ),
            'Quizzes retrieved successfully.'
        );
    }

    // نمایش یک آزمون
    public function show(
        Quiz $quiz
    ) {
        return ApiResponse::success(
            new QuizResource($quiz),
            'Quiz retrieved successfully.'
        );
    }

    // ایجاد آزمون
    public function store(
        StoreQuizRequest $request
    ) {
        $quiz = $this->quizService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new QuizResource($quiz),
            'Quiz created successfully.',
            201
        );
    }

    // بروزرسانی آزمون
    public function update(
        UpdateQuizRequest $request,
        Quiz $quiz
    ) {
        $quiz = $this->quizService->update(
            $quiz,
            $request->validated()
        );

        return ApiResponse::success(
            new QuizResource($quiz),
            'Quiz updated successfully.'
        );
    }

    // حذف آزمون
    public function destroy(
        Quiz $quiz
    ) {
        $this->quizService->delete($quiz);

        return ApiResponse::success(
            null,
            'Quiz deleted successfully.'
        );
    }
}
