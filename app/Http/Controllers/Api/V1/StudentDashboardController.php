<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ContentItem;
use App\Models\ContentProgress;
use App\Models\Grade;
use App\Models\QuestionAttempt;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentDashboardController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $subscriptions = $user->subscriptions()->where('status', 'active')
            ->where('expires_at', '>=', now())->with('plan.planable')->get();

        $bookIds = collect();
        $gradeIds = collect();
        foreach ($subscriptions as $subscription) {
            $plan = $subscription->plan;
            if (! $plan) continue;
            if ($plan->planable_type === Book::class) $bookIds->push($plan->planable_id);
            if ($plan->planable_type === Grade::class) $gradeIds->push($plan->planable_id);
        }

        if ($gradeIds->isNotEmpty()) {
            $bookIds = $bookIds->merge(Book::query()
                ->whereHas('appGradeSubject', fn ($q) => $q->whereIn('grade_id', $gradeIds))
                ->pluck('id'));
        }
        $bookIds = $bookIds->unique()->values();

        $books = Book::query()->whereIn('id', $bookIds)
            ->with(['appGradeSubject.grade', 'appGradeSubject.subject'])->get();

        $completedQuizIds = QuizAttempt::query()->where('user_id', $user->id)
            ->where('status', 'completed')->pluck('quiz_id')->unique();

        $bookCards = $books->map(function (Book $book) use ($user, $completedQuizIds) {
            $chapterIds = $book->chapters()->pluck('id');
            $sectionIds = \App\Models\Section::query()->whereIn('chapter_id', $chapterIds)->pluck('id');
            $contentIds = ContentItem::query()->where('status', 'approved')
                ->where(fn ($q) => $q->whereIn('chapter_id', $chapterIds)->orWhereIn('section_id', $sectionIds))
                ->pluck('id');
            $completedContent = ContentProgress::query()->where('user_id', $user->id)
                ->whereIn('content_item_id', $contentIds)->where('completed', true)->count();
            $quizIds = Quiz::query()->where('is_template', false)->where('status', 'active')
                ->where(fn ($q) => $q
                    ->where(fn ($x) => $x->where('quizable_type', Book::class)->where('quizable_id', $book->id))
                    ->orWhere(fn ($x) => $x->where('quizable_type', \App\Models\Chapter::class)->whereIn('quizable_id', $chapterIds))
                    ->orWhere(fn ($x) => $x->where('quizable_type', \App\Models\Section::class)->whereIn('quizable_id', $sectionIds)))
                ->pluck('id');
            $doneQuizzes = $quizIds->intersect($completedQuizIds)->count();
            $totalSteps = $contentIds->count() + $quizIds->count();
            $doneSteps = $completedContent + $doneQuizzes;
            $chapterProgress = $book->chapters()->where('is_active', true)->with('sections')->orderBy('sort_order')->get()
                ->map(function ($chapter) use ($user) {
                    $sectionIds = $chapter->sections->where('is_active', true)->pluck('id');
                    $ids = ContentItem::query()->where('status', 'approved')
                        ->where(fn ($q) => $q->where('chapter_id', $chapter->id)->orWhereIn('section_id', $sectionIds))
                        ->pluck('id');
                    $done = ContentProgress::query()->where('user_id', $user->id)
                        ->whereIn('content_item_id', $ids)->where('completed', true)->count();
                    return [
                        'id' => $chapter->id,
                        'title' => $chapter->title,
                        'progress' => $ids->count() ? (int) round($done * 100 / $ids->count()) : 0,
                        'completed_contents' => $done,
                        'total_contents' => $ids->count(),
                        'lessons' => $chapter->sections->where('is_active', true)->values()->map(fn ($section) => [
                            'id' => $section->id, 'title' => $section->title,
                        ]),
                    ];
                });

            return [
                'id' => $book->id,
                'title' => $book->title,
                'cover' => $book->cover ? Storage::disk('public')->url($book->cover) : null,
                'grade' => $book->appGradeSubject?->grade?->title,
                'grade_number' => $book->appGradeSubject?->grade?->grade_number,
                'subject' => $book->appGradeSubject?->subject?->title,
                'progress' => $totalSteps ? (int) round($doneSteps * 100 / $totalSteps) : 0,
                'completed_contents' => $completedContent,
                'total_contents' => $contentIds->count(),
                'remaining_quizzes' => max(0, $quizIds->count() - $doneQuizzes),
                'chapters' => $chapterProgress,
            ];
        })->values();

        $latestProgress = ContentProgress::query()->where('user_id', $user->id)
            ->with('contentItem.chapter.book.appGradeSubject.grade', 'contentItem.section.chapter.book.appGradeSubject.grade')
            ->latest('last_viewed_at')->first();

        $recentAttempts = QuizAttempt::query()->where('user_id', $user->id)
            ->where('status', 'completed')->with('quiz')->latest('finished_at')->limit(6)->get();

        $topicPerformance = QuestionAttempt::query()
            ->whereHas('quizAttempt', fn ($q) => $q->where('user_id', $user->id)->where('status', 'completed'))
            ->with('question.section')->get()->groupBy(fn ($answer) => $answer->question?->section?->title ?? 'مرور کلی')
            ->map(fn ($items, $title) => [
                'title' => $title,
                'percentage' => (int) round($items->where('is_correct', true)->count() * 100 / max(1, $items->count())),
            ])->sortByDesc('percentage')->values();

        $lastItem = $latestProgress?->contentItem;
        $lastBook = $lastItem?->chapter?->book ?? $lastItem?->section?->chapter?->book;

        return ApiResponse::success([
            'student' => ['name' => $user->name],
            'books' => $bookCards,
            'continue_learning' => $lastItem ? [
                'content_id' => $lastItem->id,
                'title' => $lastItem->title,
                'book_id' => $lastBook?->id,
                'book_title' => $lastBook?->title,
                'chapter_id' => $lastItem->chapter_id ?? $lastItem->section?->chapter_id,
                'section_id' => $lastItem->section_id,
                'page_number' => $lastItem->page_number,
                'last_position_seconds' => $latestProgress->last_position_seconds,
            ] : null,
            'summary' => [
                'books_count' => $bookCards->count(),
                'completed_contents' => $bookCards->sum('completed_contents'),
                'remaining_quizzes' => $bookCards->sum('remaining_quizzes'),
                'average_score' => (int) round($recentAttempts->avg('percentage') ?? 0),
            ],
            'recent_results' => $recentAttempts->map(fn ($attempt) => [
                'id' => $attempt->id, 'quiz_title' => $attempt->quiz?->title,
                'percentage' => (int) round((float) $attempt->percentage),
                'finished_at' => $attempt->finished_at?->toIso8601String(),
            ]),
            'strengths' => $topicPerformance->where('percentage', '>=', 75)->take(3)->values(),
            'needs_practice' => $topicPerformance->where('percentage', '<', 75)->sortBy('percentage')->take(3)->values(),
            'chart' => $recentAttempts->reverse()->values()->map(fn ($attempt, $index) => [
                'label' => 'آزمون '.($index + 1), 'percentage' => (int) round((float) $attempt->percentage),
            ]),
        ], 'داشبورد دانش‌آموز دریافت شد.');
    }

    public function saveProgress(Request $request, ContentItem $contentItem)
    {
        abort_unless($contentItem->is_free || $request->user()->hasAccessToContentItem($contentItem), 403, 'به این محتوا دسترسی ندارید.');
        $data = $request->validate([
            'watch_seconds' => ['nullable', 'integer', 'min:0'],
            'last_position_seconds' => ['nullable', 'integer', 'min:0'],
            'completed' => ['nullable', 'boolean'],
        ]);
        $progress = ContentProgress::query()->firstOrNew([
            'user_id' => $request->user()->id, 'content_item_id' => $contentItem->id,
        ]);
        $progress->watch_seconds = max($progress->watch_seconds ?? 0, $data['watch_seconds'] ?? 0);
        $progress->last_position_seconds = $data['last_position_seconds'] ?? $progress->last_position_seconds ?? 0;
        $progress->last_viewed_at = now();
        if (($data['completed'] ?? false) && ! $progress->completed) {
            $progress->completed = true;
            $progress->completed_at = now();
        }
        $progress->save();

        return ApiResponse::success(['saved' => true], 'پیشرفت ذخیره شد.');
    }

    public function contentProgress(Request $request, ContentItem $contentItem)
    {
        $progress = ContentProgress::query()->where('user_id', $request->user()->id)
            ->where('content_item_id', $contentItem->id)->first();

        return ApiResponse::success([
            'last_position_seconds' => $progress?->last_position_seconds ?? 0,
            'watch_seconds' => $progress?->watch_seconds ?? 0,
            'completed' => $progress?->completed ?? false,
        ], 'موقعیت مطالعه دریافت شد.');
    }
}
