<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Section;
use App\Models\User;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
    ): LengthAwarePaginator {
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
    ): LengthAwarePaginator {
        return $this->quizAttemptRepository->paginate($perPage);
    }

    public function findById(
        int $id
    ): ?QuizAttempt {
        return $this->quizAttemptRepository->findById($id);
    }

    public function start(
        Quiz $quiz,
        User $user
    ): QuizAttempt {
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

                // سؤال‌ها مستقیماً از بانک سؤال و بر اساس دامنه‌ی
                // آزمون خوانده می‌شوند؛ نیازی به اتصال دستی سؤال به
                // آزمون نیست. هر تلاش نمونه‌ی تصادفی تازه‌ای می‌گیرد.
                $questions = Question::query()
                    ->where('status', 'approved')
                    ->where('is_active', true)
                    ->whereHas('options')
                    ->with('options')
                    ->when($quiz->quizable_type === Section::class, fn ($query) => $query
                        ->where('section_id', $quiz->quizable_id))
                    ->when($quiz->quizable_type === Chapter::class, fn ($query) => $query
                        ->where('chapter_id', $quiz->quizable_id))
                    ->when($quiz->quizable_type === Book::class, fn ($query) => $query
                        ->where('book_id', $quiz->quizable_id))
                    ->when($quiz->randomize_questions, fn ($query) => $query->inRandomOrder())
                    ->when(! $quiz->randomize_questions, fn ($query) => $query->orderBy('id'))
                    ->limit($quiz->questions_count)
                    ->get();

                if ($questions->isEmpty()) {
                    throw new \RuntimeException('برای این محدوده هنوز سؤال تأییدشده‌ای در بانک سؤال وجود ندارد.');
                }

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

                            'image_path' => $question->image_path
                                ? Storage::disk('public')->url($question->image_path)
                                : null,

                            'difficulty' => $question->difficulty,

                        ],

                        'options_snapshot' => $options
                            ->map(fn ($option) => [

                                'id' => $option->id,

                                'text' => $option->option_text,

                                'image_path' => $option->image_path
                                    ? Storage::disk('public')->url($option->image_path)
                                    : null,

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
    ): QuestionAttempt {

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
    ): QuizAttempt {

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
                fn ($item) => $item->question->default_score
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
    ): array {
        $attempt->load([
            'quiz.quizable',
            'questionAttempts.question.options',
            'questionAttempts.selectedOption',
        ]);

        $percentage = (float) $attempt->percentage;
        $grade = $attempt->quiz->book?->appGradeSubject?->grade;
        $descriptive = match (true) {
            $percentage >= 90 => ['label' => 'خیلی خوب', 'message' => 'آفرین! یادگیری تو عمیق و دقیق است.', 'tone' => 'excellent'],
            $percentage >= 75 => ['label' => 'خوب', 'message' => 'عملکرد خوبی داشتی؛ با یک مرور کوتاه عالی‌تر می‌شوی.', 'tone' => 'good'],
            $percentage >= 50 => ['label' => 'قابل قبول', 'message' => 'در مسیر درستی هستی؛ پاسخ‌های اشتباه را دوباره تمرین کن.', 'tone' => 'acceptable'],
            default => ['label' => 'نیازمند تلاش بیشتر', 'message' => 'نگران نباش؛ گزارش زیر دقیقاً نشان می‌دهد از کجا ادامه بدهی.', 'tone' => 'needs_practice'],
        };

        $reviews = $attempt->questionAttempts->values()->map(function (QuestionAttempt $answer, int $index) use ($attempt) {
            $question = $answer->question;
            $correctOption = $question?->options->firstWhere('is_correct', true);

            return [
                'number' => $index + 1,
                'question' => $answer->question_snapshot['text'] ?? $question?->question_text,
                'question_image' => ($answer->question_snapshot['image_path'] ?? null)
                    ?: ($question?->image_path ? Storage::disk('public')->url($question->image_path) : null),
                'is_correct' => (bool) $answer->is_correct,
                'is_answered' => $answer->answered_at !== null,
                'selected_answer' => $answer->selectedOption?->option_text,
                'selected_answer_image' => $answer->selectedOption?->image_path
                    ? Storage::disk('public')->url($answer->selectedOption->image_path)
                    : null,
                // در صفحه مرور نتیجه، دانش‌آموز باید دقیقاً بداند
                // پاسخ درست چه بوده است؛ این بخش آموزشی است، نه
                // نمایش پاسخ در زمان برگزاری آزمون.
                'correct_answer' => $correctOption?->option_text,
                'correct_answer_image' => $correctOption?->image_path
                    ? Storage::disk('public')->url($correctOption->image_path)
                    : null,
                'explanation' => $question?->explanation,
                'explanation_image' => $question?->explanation_image_path
                    ? Storage::disk('public')->url($question->explanation_image_path)
                    : null,
                'recommendation' => $question?->recommendation_text,
                'difficulty' => $question?->difficulty,
            ];
        });

        return [
            'quiz' => [
                'id' => $attempt->quiz->id,
                'title' => $attempt->quiz->title,
            ],
            'grade' => $grade ? [
                'id' => $grade->id,
                'number' => $grade->grade_number,
                'title' => $grade->title,
                'is_primary' => in_array((int) $grade->grade_number, range(1, 6), true),
            ] : null,
            'score' => [
                'total' => $attempt->total_score,
                'earned' => $attempt->earned_score,
                'percentage' => $percentage,
            ],
            'statistics' => [
                'correct_answers' => $attempt->correct_answers_count,
                'wrong_answers' => $attempt->wrong_answers_count,
                'unanswered' => $attempt->unanswered_count,
            ],
            'status' => $attempt->percentage >= $attempt->quiz->passing_percentage
                ? 'passed'
                : 'failed',
            'descriptive_assessment' => $descriptive,
            'feedback' => $reviews,
        ];
    }
}
