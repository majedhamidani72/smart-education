<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuizAttempt\StoreQuizAttemptRequest;
use App\Http\Requests\QuizAttempt\UpdateQuizAttemptRequest;
use App\Http\Resources\QuizAttemptResource;
use App\Models\QuizAttempt;
use App\Services\QuizAttemptService;

class QuizAttemptController extends Controller
{
    protected QuizAttemptService $quizAttemptService;

    public function __construct(
        QuizAttemptService $quizAttemptService
    ) {
        $this->quizAttemptService = $quizAttemptService;
    }

    // لیست تلاش‌ها
    public function index()
    {
        return ApiResponse::success(
            QuizAttemptResource::collection(
                $this->quizAttemptService->getAll()
            ),
            'Quiz attempts retrieved successfully.'
        );
    }

    // نمایش یک تلاش
    public function show(
        QuizAttempt $quizAttempt
    ) {
        return ApiResponse::success(
            new QuizAttemptResource(
                $quizAttempt
            ),
            'Quiz attempt retrieved successfully.'
        );
    }

    // ایجاد تلاش
    public function store(
        StoreQuizAttemptRequest $request
    ) {
        $quizAttempt = $this->quizAttemptService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new QuizAttemptResource(
                $quizAttempt
            ),
            'Quiz attempt created successfully.',
            201
        );
    }

    // بروزرسانی تلاش
    public function update(
        UpdateQuizAttemptRequest $request,
        QuizAttempt $quizAttempt
    ) {
        $quizAttempt = $this->quizAttemptService->update(
            $quizAttempt,
            $request->validated()
        );

        return ApiResponse::success(
            new QuizAttemptResource(
                $quizAttempt
            ),
            'Quiz attempt updated successfully.'
        );
    }

    // حذف تلاش
    public function destroy(
        QuizAttempt $quizAttempt
    ) {
        $this->quizAttemptService->delete(
            $quizAttempt
        );

        return ApiResponse::success(
            null,
            'Quiz attempt deleted successfully.'
        );
    }
}
