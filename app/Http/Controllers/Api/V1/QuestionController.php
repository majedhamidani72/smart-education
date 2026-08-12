<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Question;
use App\Helpers\ApiResponse;
use App\Services\QuestionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;

class QuestionController extends Controller
{
    protected QuestionService $questionService;


    public function __construct(
        QuestionService $questionService
    ) {
        $this->questionService = $questionService;
    }


    /**
     * لیست سوالات
     */
    public function index()
    {
        $this->authorize(
            'viewAny',
            Question::class
        );


        $questions = $this->questionService->paginate();


        return ApiResponse::success(
            QuestionResource::collection($questions),
            'Questions retrieved successfully.'
        );
    }


    /**
     * نمایش سوال
     */
    public function show(
        Question $question
    )
    {
        $this->authorize(
            'view',
            $question
        );


        $question->load([
            'topic',
            'options',
            'creator',
            'reviewer',
        ]);


        return ApiResponse::success(
            new QuestionResource($question),
            'Question retrieved successfully.'
        );
    }


    /**
     * ایجاد سوال
     */
    public function store(
        StoreQuestionRequest $request
    )
    {
        $this->authorize(
            'create',
            Question::class
        );


        $question = $this->questionService->create(
            $request->validated()
        );


        return ApiResponse::success(
            new QuestionResource($question),
            'Question created successfully.',
            201
        );
    }


    /**
     * بروزرسانی سوال
     */
    public function update(
        UpdateQuestionRequest $request,
        Question $question
    )
    {
        $this->authorize(
            'update',
            $question
        );


        $question = $this->questionService->update(
            $question,
            $request->validated()
        );


        return ApiResponse::success(
            new QuestionResource($question),
            'Question updated successfully.'
        );
    }


    /**
     * حذف سوال
     */
    public function destroy(
        Question $question
    )
    {
        $this->authorize(
            'delete',
            $question
        );


        $this->questionService->delete(
            $question
        );


        return ApiResponse::success(
            null,
            'Question deleted successfully.'
        );
    }
}
