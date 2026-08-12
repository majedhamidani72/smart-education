<?php

namespace App\Services;

use Throwable;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Models\QuestionAttempt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\QuestionAttemptRepositoryInterface;

class QuestionAttemptService
{
    protected QuestionAttemptRepositoryInterface $questionAttemptRepository;


    public function __construct(
        QuestionAttemptRepositoryInterface $questionAttemptRepository
    ) {
        $this->questionAttemptRepository = $questionAttemptRepository;
    }



    public function getAll(): Collection
    {
        return $this->questionAttemptRepository->getAll();
    }



    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->questionAttemptRepository->paginate($perPage);
    }



    /**
     * دریافت پاسخ‌های یک کاربر
     */
    public function getByUser(
        User $user
    ): Collection {
        return QuestionAttempt::whereHas(
            'quizAttempt',
            function ($query) use ($user) {

                $query->where(
                    'user_id',
                    $user->id
                );
            }
        )
            ->with([
                'question',
                'selectedOption',
                'quizAttempt',
            ])
            ->latest()
            ->get();
    }



    /**
     * ثبت پاسخ دانش‌آموز
     */
    public function submitAnswer(
        QuizAttempt $quizAttempt,
        array $data
    ): QuestionAttempt {
        try {

            return DB::transaction(function () use ($quizAttempt, $data) {


                $questionAttempt = QuestionAttempt::where(
                    'quiz_attempt_id',
                    $quizAttempt->id
                )
                    ->where(
                        'question_id',
                        $data['question_id']
                    )
                    ->firstOrFail();



                $option = null;


                if (!empty($data['question_option_id'])) {

                    $option = $questionAttempt
                        ->question
                        ->options()
                        ->where(
                            'id',
                            $data['question_option_id']
                        )
                        ->first();
                }



                $isCorrect = $option?->is_correct ?? false;



                $score = $isCorrect
                    ? $questionAttempt->question->default_score
                    : 0;



                $questionAttempt->update([

                    'question_option_id' => $option?->id,

                    'is_correct' => $isCorrect,

                    'score_awarded' => $score,

                    'answered_at' => now(),

                ]);



                return $questionAttempt->fresh([
                    'question',
                    'selectedOption',
                ]);
            });
        } catch (Throwable $e) {


            Log::error(
                'Question answer submission failed.',
                [
                    'quiz_attempt_id' => $quizAttempt->id,

                    'data' => $data,

                    'error' => $e->getMessage(),

                ]
            );


            throw $e;
        }
    }



    /**
     * دریافت پاسخ‌های یک آزمون
     */
    public function getByQuizAttempt(
        QuizAttempt $quizAttempt
    ): Collection {
        return $quizAttempt
            ->questionAttempts()
            ->with([
                'question',
                'selectedOption',
            ])
            ->get();
    }



    /**
     * دریافت یک پاسخ
     */
    public function findById(
        int $id
    ): ?QuestionAttempt {
        return $this->questionAttemptRepository
            ->findById($id);
    }
}
