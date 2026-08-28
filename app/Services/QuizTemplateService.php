<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QuizTemplateService
{
    private const SETTINGS = [
        'description', 'questions_count', 'time_limit', 'passing_percentage',
        'max_attempts', 'randomize_questions', 'randomize_options', 'show_result',
        'show_correct_answers', 'is_free', 'status', 'reviewed_by',
        'rejection_reason', 'published_at', 'term_scope',
    ];

    public function sync(Quiz $template): void
    {
        if (! $template->is_template || ! $template->template_book_id) {
            return;
        }

        DB::transaction(function () use ($template) {
            $book = Book::with(['appGradeSubject.subject', 'chapters.sections'])->findOrFail($template->template_book_id);
            $targets = $this->targets($book, $template->template_scope)
                ->filter(fn (Model $target) => $this->hasQuestions($target));
            $targetKeys = [];

            foreach ($targets as $target) {
                $targetKeys[] = $target::class.'#'.$target->getKey();
                $quiz = Quiz::withTrashed()
                    ->where('quizable_type', $target::class)
                    ->where('quizable_id', $target->getKey())
                    ->where(fn ($q) => $q->where('template_id', $template->id)->orWhereNull('template_id'))
                    ->where('is_template', false)
                    ->latest('id')->first() ?? new Quiz();

                $quiz->fill(array_merge(
                    $template->only(self::SETTINGS),
                    [
                        'template_id' => $template->id,
                        'template_book_id' => $book->id,
                        'quizable_type' => $target::class,
                        'quizable_id' => $target->getKey(),
                        'created_by' => $template->created_by,
                        'title' => $this->title($book, $target),
                        // مقدار الگو «حداکثر» است؛ هیچ آزمونی بیشتر
                        // از موجودی واقعی بانک همان محدوده سؤال نمی‌گیرد.
                        'questions_count' => min(
                            $template->questions_count,
                            $this->availableQuestions($target)
                        ),
                        'is_template' => false,
                    ]
                ));
                $quiz->deleted_at = null;
                $quiz->save();
            }

            $template->generatedQuizzes()->get()->each(function (Quiz $quiz) use ($targetKeys) {
                if (! in_array($quiz->quizable_type.'#'.$quiz->quizable_id, $targetKeys, true)) {
                    $quiz->delete();
                }
            });
        });
    }

    public function syncBook(Book|int $book): void
    {
        $bookId = $book instanceof Book ? $book->id : $book;
        Quiz::query()->where('is_template', true)->where('template_book_id', $bookId)
            ->get()->each(fn (Quiz $template) => $this->sync($template));
    }

    private function targets(Book $book, ?string $type)
    {
        return match ($type) {
            Section::class => $book->chapters->where('is_active', true)->flatMap(
                fn ($chapter) => $chapter->sections->where('is_active', true)
            ),
            Chapter::class => $book->chapters->where('is_active', true),
            Book::class => collect([$book]),
            default => collect(),
        };
    }

    private function title(Book $book, Model $target): string
    {
        if ($target instanceof Section) {
            $lessonMode = $book->appGradeSubject?->subject?->exam_structure === 'lesson_term';
            return ($lessonMode ? 'آزمون درس ' : 'آزمون بخش ').$target->title;
        }
        if ($target instanceof Chapter) {
            return 'آزمون فصل '.$target->title;
        }
        return 'آزمون جامع '.$book->title;
    }

    private function hasQuestions(Model $target): bool
    {
        return $this->availableQuestions($target) > 0;
    }

    private function availableQuestions(Model $target): int
    {
        return Question::query()
            ->where('status', 'approved')->where('is_active', true)
            ->whereHas('options')
            ->when($target instanceof Section, fn ($q) => $q->where('section_id', $target->id))
            ->when($target instanceof Chapter, fn ($q) => $q->where('chapter_id', $target->id))
            ->when($target instanceof Book, fn ($q) => $q->where('book_id', $target->id))
            ->count();
    }
}
