<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\QuizAttempt;
use App\Helpers\ApiResponse;
use App\Services\QuizAttemptService;
use App\Services\QuestionAttemptService;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizAttemptResource;
use App\Http\Resources\QuestionAttemptResource;
use Illuminate\Http\Request;

class QuizAttemptController extends Controller
{
    protected QuizAttemptService $quizAttemptService;

    protected QuestionAttemptService $questionAttemptService;


    public function __construct(
        QuizAttemptService $quizAttemptService,
        QuestionAttemptService $questionAttemptService
    ) {
        $this->quizAttemptService = $quizAttemptService;

        $this->questionAttemptService = $questionAttemptService;
    }



    /**
     * لیست تلاش‌های کاربر
     */
    public function index(
        Request $request
    )
    {
        $attempts = $this->quizAttemptService
            ->userAttempts(
                $request->user()
            );


        return ApiResponse::success(
            QuizAttemptResource::collection($attempts),
            'Quiz attempts retrieved successfully.'
        );
    }



    /**
     * نمایش یک تلاش
     */
    public function show(
        QuizAttempt $attempt
    )
    {
        $this->authorize(
            'view',
            $attempt
        );


        $attempt->load([
            'quiz',
            'questionAttempts.question',
            'questionAttempts.selectedOption',
        ]);


        return ApiResponse::success(
            new QuizAttemptResource($attempt),
            'Quiz attempt retrieved successfully.'
        );
    }



    /**
     * ثبت پاسخ سوال
     */
    public function answer(
        Request $request,
        QuizAttempt $attempt
    )
    {
        $this->authorize(
            'update',
            $attempt
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



        $answer = $this->questionAttemptService
            ->submitAnswer(
                $attempt,
                $data
            );


        return ApiResponse::success(
            new QuestionAttemptResource($answer),
            'Answer submitted successfully.'
        );
    }



    /**
     * پایان آزمون
     */
    public function finish(
        QuizAttempt $attempt
    )
    {
        $this->authorize(
            'update',
            $attempt
        );


        $attempt = $this->quizAttemptService
            ->finish(
                $attempt
            );


        return ApiResponse::success(
            new QuizAttemptResource($attempt),
            'Quiz finished successfully.'
        );
    }



    /**
     * نتیجه آزمون
     */
    public function result(
        QuizAttempt $attempt
    )
    {
        $this->authorize(
            'view',
            $attempt
        );


        $result = $this->quizAttemptService
            ->result(
                $attempt
            );


        return ApiResponse::success(
            $result,
            'Quiz result retrieved successfully.'
        );
    }
}
