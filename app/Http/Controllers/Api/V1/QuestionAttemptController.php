<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionAttempt\StoreQuestionAttemptRequest;
use App\Http\Requests\QuestionAttempt\UpdateQuestionAttemptRequest;
use App\Http\Resources\QuestionAttemptResource;
use App\Models\QuestionAttempt;
use App\Services\QuestionAttemptService;

class QuestionAttemptController extends Controller
{
    protected QuestionAttemptService $questionAttemptService;

    public function __construct(
        QuestionAttemptService $questionAttemptService
    ) {
        $this->questionAttemptService = $questionAttemptService;
    }

    // لیست پاسخ‌ها
    public function index()
    {
        return ApiResponse::success(
            QuestionAttemptResource::collection(
                $this->questionAttemptService->getAll()
            ),
            'Question attempts retrieved successfully.'
        );
    }

    // نمایش یک پاسخ
    public function show(
        QuestionAttempt $questionAttempt
    ) {
        return ApiResponse::success(
            new QuestionAttemptResource(
                $questionAttempt
            ),
            'Question attempt retrieved successfully.'
        );
    }

    // ایجاد پاسخ
    public function store(
        StoreQuestionAttemptRequest $request
    ) {
        $questionAttempt = $this->questionAttemptService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new QuestionAttemptResource(
                $questionAttempt
            ),
            'Question attempt created successfully.',
            201
        );
    }

    // بروزرسانی پاسخ
    public function update(
        UpdateQuestionAttemptRequest $request,
        QuestionAttempt $questionAttempt
    ) {
        $questionAttempt = $this->questionAttemptService->update(
            $questionAttempt,
            $request->validated()
        );

        return ApiResponse::success(
            new QuestionAttemptResource(
                $questionAttempt
            ),
            'Question attempt updated successfully.'
        );
    }

    // حذف پاسخ
    public function destroy(
        QuestionAttempt $questionAttempt
    ) {
        $this->questionAttemptService->delete(
            $questionAttempt
        );

        return ApiResponse::success(
            null,
            'Question attempt deleted successfully.'
        );
    }
}
