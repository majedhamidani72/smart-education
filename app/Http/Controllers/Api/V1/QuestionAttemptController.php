<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\QuestionAttempt;
use App\Models\QuizAttempt;
use App\Helpers\ApiResponse;
use App\Services\QuestionAttemptService;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionAttemptResource;
use Illuminate\Http\Request;

class QuestionAttemptController extends Controller
{
    protected QuestionAttemptService $questionAttemptService;

    public function __construct(
        QuestionAttemptService $questionAttemptService
    ) {
        $this->questionAttemptService = $questionAttemptService;
    }


    /**
     * لیست پاسخ‌ها
     */
    public function index(
        Request $request
    )
    {
        $attempts = $this->questionAttemptService
            ->getByUser(
                $request->user()
            );


        return ApiResponse::success(
            QuestionAttemptResource::collection($attempts),
            'Question attempts retrieved successfully.'
        );
    }


    /**
     * نمایش یک پاسخ
     */
    public function show(
        QuestionAttempt $questionAttempt
    )
    {
        $this->authorize(
            'view',
            $questionAttempt
        );


        $questionAttempt->load([
            'question',
            'selectedOption',
            'quizAttempt',
        ]);


        return ApiResponse::success(
            new QuestionAttemptResource($questionAttempt),
            'Question attempt retrieved successfully.'
        );
    }


    /**
     * ثبت پاسخ
     */
    public function store(
        Request $request,
        QuizAttempt $quizAttempt
    )
    {
        $this->authorize(
            'update',
            $quizAttempt
        );


        $data = $request->validate([

            'question_id' => [
                'required',
                'exists:questions,id',
            ],

            'question_option_id' => [
                'nullable',
                'exists:question_options,id',
            ],

        ]);


        $questionAttempt = $this->questionAttemptService
            ->submitAnswer(
                $quizAttempt,
                $data
            );


        return ApiResponse::success(
            new QuestionAttemptResource($questionAttempt),
            'Answer submitted successfully.',
            201
        );
    }
}
