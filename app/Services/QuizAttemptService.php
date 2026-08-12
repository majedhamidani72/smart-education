<?php

namespace App\Services;

use Throwable;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuestionAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;

class QuizAttemptService
{
    protected QuizAttemptRepositoryInterface $quizAttemptRepository;


    public function __construct(
        QuizAttemptRepositoryInterface $quizAttemptRepository
    ) {
        $this->quizAttemptRepository = $quizAttemptRepository;
    }


    public function userAttempts(
        User $user
    ): LengthAwarePaginator
    {
        return QuizAttempt::where(
            'user_id',
            $user->id
        )
        ->latest()
        ->paginate(15);
    }


    public function getAll(): Collection
    {
        return $this->quizAttemptRepository->getAll();
    }


    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->quizAttemptRepository->paginate($perPage);
    }


    public function findById(
        int $id
    ): ?QuizAttempt
    {
        return $this->quizAttemptRepository->findById($id);
    }



    public function start(
        Quiz $quiz,
        User $user
    ): QuizAttempt
    {
        try {

            return DB::transaction(function () use ($quiz, $user) {


                $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('user_id', $user->id)
                    ->count();


                if (
                    $quiz->max_attempts &&
                    $attemptCount >= $quiz->max_attempts
                ) {
                    throw new \Exception(
                        'Maximum attempts reached.'
                    );
                }


                $attempt = QuizAttempt::create([

                    'quiz_id' => $quiz->id,

                    'user_id' => $user->id,

                    'status' => 'started',

                    'started_at' => now(),

                ]);



                $questions = $quiz->questions()
                    ->when(
                        $quiz->randomize_questions,
                        fn($query) => $query->inRandomOrder()
                    )
                    ->limit($quiz->questions_count)
                    ->get();



                foreach ($questions as $question) {


                    $options = $question->options;


                    if ($quiz->randomize_options) {
                        $options = $options->shuffle();
                    }



                    QuestionAttempt::create([

                        'quiz_attempt_id' => $attempt->id,

                        'question_id' => $question->id,

                        'question_snapshot' => [

                            'id' => $question->id,

                            'text' => $question->question_text,

                        ],

                        'options_snapshot' => $options
                            ->map(fn($option) => [

                                'id' => $option->id,

                                'text' => $option->option_text,

                            ])
                            ->toArray(),

                    ]);
                }


                return $attempt->load(
                    'questionAttempts'
                );

            });


        } catch (Throwable $e) {


            Log::error('Quiz start failed.', [

                'quiz_id' => $quiz->id,

                'user_id' => $user->id,

                'error' => $e->getMessage(),

            ]);


            throw $e;
        }
    }



    public function answer(
        QuizAttempt $attempt,
        array $data
    ): QuestionAttempt
    {

        $questionAttempt = QuestionAttempt::where(
            'quiz_attempt_id',
            $attempt->id
        )
        ->where(
            'question_id',
            $data['question_id']
        )
        ->firstOrFail();



        $option = $questionAttempt
            ->question
            ->options()
            ->where(
                'id',
                $data['question_option_id']
            )
            ->first();



        $isCorrect = $option?->is_correct ?? false;



        $questionAttempt->update([

            'question_option_id' => $option?->id,

            'is_correct' => $isCorrect,

            'score_awarded' => $isCorrect
                ? $questionAttempt->question->default_score
                : 0,

            'answered_at' => now(),

        ]);



        return $questionAttempt->fresh();

    }



    public function finish(
        QuizAttempt $attempt
    ): QuizAttempt
    {

        $attempt->load(
            'questionAttempts.question'
        );


        $questions = $attempt->questionAttempts;


        $correct = $questions
            ->where('is_correct', true)
            ->count();


        $wrong = $questions
            ->where('is_correct', false)
            ->whereNotNull('answered_at')
            ->count();


        $unanswered = $questions
            ->whereNull('answered_at')
            ->count();


        $totalScore = $questions
            ->sum(
                fn($item) => $item->question->default_score
            );


        $earnedScore = $questions
            ->sum('score_awarded');


        $percentage = $totalScore > 0
            ? ($earnedScore / $totalScore) * 100
            : 0;



        $attempt->update([

            'total_score' => $totalScore,

            'earned_score' => $earnedScore,

            'percentage' => $percentage,

            'correct_answers_count' => $correct,

            'wrong_answers_count' => $wrong,

            'unanswered_count' => $unanswered,

            'status' => 'finished',

            'finished_at' => now(),

        ]);



        return $attempt->fresh();

    }



    public function result(
        QuizAttempt $attempt
    ): array
    {
        $attempt->load('quiz');


        return [

            'percentage' => $attempt->percentage,

            'correct_answers' => $attempt->correct_answers_count,

            'wrong_answers' => $attempt->wrong_answers_count,

            'unanswered' => $attempt->unanswered_count,

            'passed' => $attempt->percentage >= $attempt->quiz->passing_percentage,

        ];
    }
}
