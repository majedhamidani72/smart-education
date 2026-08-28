<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Quiz;
use App\Helpers\ApiResponse;
use App\Services\QuizService;
use App\Services\QuizAttemptService;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Http\Resources\QuizAttemptResource;
use App\Http\Requests\Quiz\StoreQuizRequest;
use App\Http\Requests\Quiz\UpdateQuizRequest;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    protected QuizService $quizService;

    protected QuizAttemptService $quizAttemptService;


    public function __construct(
        QuizService $quizService,
        QuizAttemptService $quizAttemptService
    ) {
        $this->quizService = $quizService;
        $this->quizAttemptService = $quizAttemptService;
    }


    /**
     * لیست آزمون‌ها
     */
    public function index()
    {
        $quizzes = $this->quizService->paginate();


        return ApiResponse::success(
            QuizResource::collection($quizzes),
            'Quizzes retrieved successfully.'
        );
    }


    /**
     * نمایش یک آزمون
     */
    public function show(
        Quiz $quiz
    )
    {
        // بدون این چک، حتی صورت سوالات آزمون‌های پولی هم بدون
        // خرید قابل مشاهده بود.
        if (! $quiz->is_free && ! auth('sanctum')->user()?->hasAccessToQuiz($quiz)) {

            return ApiResponse::error(
                'برای مشاهده‌ی این آزمون، ابتدا باید آن را خریداری کنید.',
                null,
                403
            );
        }

        $quiz = $this->quizService->loadRelations($quiz);


        return ApiResponse::success(
            new QuizResource($quiz),
            'Quiz retrieved successfully.'
        );
    }


    /**
     * شروع آزمون
     */
    public function start(
        Request $request,
        Quiz $quiz
    )
    {
        // بدون این چک، حتی آزمون‌های پولی هم بدون خرید قابل شروع
        // بودند — یک شکاف واقعی که همینجا بسته می‌شود. توجه: این
        // با QuizPolicy::view() فرق دارد — آن یکی چک مالکیت معلم
        // (برای پنل ادمین) است، نه چک خریدِ دانش‌آموز.
        if (! $quiz->is_free && ! $request->user()->hasAccessToQuiz($quiz)) {

            return ApiResponse::error(
                'برای شروع این آزمون، ابتدا باید آن را خریداری کنید.',
                null,
                403
            );
        }

        $attempt = $this->quizAttemptService->start(
            $quiz,
            $request->user()
        );


        return ApiResponse::success(
            new QuizAttemptResource($attempt),
            'Quiz started successfully.',
            201
        );
    }


    /**
     * ایجاد آزمون
     */
    public function store(
        StoreQuizRequest $request
    )
    {
        $this->authorize(
            'create',
            Quiz::class
        );


        $quiz = $this->quizService->create(
            $request->validated()
        );


        return ApiResponse::success(
            new QuizResource($quiz),
            'Quiz created successfully.',
            201
        );
    }


    /**
     * بروزرسانی آزمون
     */
    public function update(
        UpdateQuizRequest $request,
        Quiz $quiz
    )
    {
        $this->authorize(
            'update',
            $quiz
        );


        $quiz = $this->quizService->update(
            $quiz,
            $request->validated()
        );


        return ApiResponse::success(
            new QuizResource($quiz),
            'Quiz updated successfully.'
        );
    }


    /**
     * حذف آزمون
     */
    public function destroy(
        Quiz $quiz
    )
    {
        $this->authorize(
            'delete',
            $quiz
        );


        $this->quizService->delete(
            $quiz
        );


        return ApiResponse::success(
            null,
            'Quiz deleted successfully.'
        );
    }
}
