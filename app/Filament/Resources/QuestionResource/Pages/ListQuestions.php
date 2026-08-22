<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

/**
 * لیست بانک سوالات — به‌صورت سه‌سطحی (نه یک لیست تخت)
 * --------------------------------------------------------------------
 * سطح ۱: هر ردیف یک ترکیب «اپلیکیشن + ایجادکننده + پایه + کتاب»ست.
 * سطح ۲: بعد از انتخاب یکی از بالا، سه ردیف: آزمون‌های بخش /
 *        فصل / کل کتاب — هرکدام با تعداد سوالات همان دسته.
 * سطح ۳: لیست واقعی سوالات همان دسته، به تفکیک فصل/بخش و
 *        قابل‌جمع‌شدن (تا با تعداد زیاد بخش، صفحه شلوغ نشود).
 * --------------------------------------------------------------------
 * نکته‌ی مهم فنی: همه‌جا مستقیم از book_id/chapter_id/section_id
 * خودِ سوال استفاده می‌شود (نه از طریق content_item_id) — چون اگر
 * سوال به تنهایی (بدون این‌که هنوز محتوای دیگری در آن فصل باشد)
 * ساخته شود، content_item_id همیشه پیدا نمی‌شود و سوال هیچ‌جا
 * نمایش داده نمی‌شد. این دقیقاً همان کلاس باگی بود که قبلاً برای
 * content_items با ستون مستقیم chapter_id حل شد.
 */
class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected static string $view = 'filament.resources.question-resource.pages.list-questions';

    public string $viewLevel = 'groups';

    public ?int $selectedAppId = null;

    public ?int $selectedCreatorId = null;

    public ?int $selectedGradeId = null;

    public ?int $selectedBookId = null;

    public ?string $selectedExamLevel = null;

    protected function getHeaderActions(): array
    {
        return [

            \Filament\Actions\Action::make('create')
                ->label('ایجاد سوال')
                ->icon('heroicon-o-plus')
                ->url(\App\Filament\Pages\AddQuestionsToBank::getUrl()),

        ];
    }

    protected function isReviewer(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('SuperAdmin') || $user?->hasRole('Admin');
    }

    /**
     * کوئری پایه — همان محدودیتِ «معلم فقط سوالات خودش را می‌بیند»
     * که در Resource تعریف شده. چیزی مخفی نمی‌شود؛ سوالات پیش‌نویس
     * هم دیده می‌شوند، فقط دکمه‌های تایید/رد رویشان ظاهر نمی‌شود.
     */
    protected function baseQuery()
    {
        return QuestionResource::getEloquentQuery();
    }

    /**
     * سطح ۱: ترکیب‌های یکتای اپلیکیشن/ایجادکننده/پایه/کتاب —
     * مستقیم از book_id خودِ سوال (نه از طریق محتوا).
     */
    public function getGroups()
    {
        $questions = $this->baseQuery()
            ->with(['creator', 'book.appGradeSubject.grade', 'book.appGradeSubject.app'])
            ->whereNotNull('book_id')
            ->get();

        return $questions
            ->groupBy(function ($q) {

                return $q->book->appGradeSubject?->app_id.'-'
                    .$q->created_by.'-'
                    .$q->book->appGradeSubject?->grade_id.'-'
                    .$q->book_id;
            })
            ->map(function ($group) {

                $first = $group->first();

                $book = $first->book;

                return [
                    'app_id' => $book->appGradeSubject?->app_id,
                    'app_title' => $book->appGradeSubject?->app?->title ?? '—',
                    'creator_id' => $first->created_by,
                    'creator_title' => $first->creator?->name ?? '—',
                    'grade_id' => $book->appGradeSubject?->grade_id,
                    'grade_title' => $book->appGradeSubject?->grade?->title ?? '—',
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'count' => $group->count(),
                    'pending_count' => $group->where('status', 'pending')->count(),
                ];
            })
            ->sortBy(fn($g) => $g['grade_id'])
            ->values();
    }

    public function selectGroup($appId, $creatorId, $gradeId, $bookId): void
    {
        $this->selectedAppId = $appId;
        $this->selectedCreatorId = $creatorId;
        $this->selectedGradeId = $gradeId;
        $this->selectedBookId = $bookId;
        $this->viewLevel = 'examLevels';
    }

    /**
     * سطح ۲: تعداد سوالات به تفکیک «بخش» (section_id ست شده)،
     * «فصل» (فقط chapter_id، بدون section)، و «کل کتاب» (فقط
     * book_id، بدون فصل مشخص — حالا که مستقیم روی خودِ سوال قابل
     * تشخیص است، این حالت هم واقعاً کار می‌کند).
     */
    public function getExamLevelCounts(): array
    {
        $base = fn() => $this->baseQuery()
            ->where('created_by', $this->selectedCreatorId)
            ->where('book_id', $this->selectedBookId);

        return [

            'section' => $base()->whereNotNull('section_id')->count(),

            'chapter' => $base()->whereNull('section_id')->whereNotNull('chapter_id')->count(),

            'book' => $base()->whereNull('chapter_id')->count(),

        ];
    }

    public function selectExamLevel(string $level): void
    {
        $this->selectedExamLevel = $level;
        $this->viewLevel = 'list';
    }

    /**
     * سطح ۳: لیست واقعی سوالات همان دسته‌ی انتخاب‌شده.
     */
    public function getFilteredQuestions()
    {
        $query = $this->baseQuery()
            ->where('created_by', $this->selectedCreatorId)
            ->where('book_id', $this->selectedBookId)
            ->with(['section', 'chapter']);

        if ($this->selectedExamLevel === 'section') {
            $query->whereNotNull('section_id');
        } elseif ($this->selectedExamLevel === 'chapter') {
            $query->whereNull('section_id')->whereNotNull('chapter_id');
        } else {
            $query->whereNull('chapter_id');
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * همان لیست سطح ۳، ولی این‌بار به تفکیک فصل/بخش دقیق گروه‌بندی
     * شده — هر زیرگروه دکمه‌ی «ادامه‌ی افزودن سوال» مخصوص به خودش
     * را دارد، و برای ادمین/سوپرادمین، دکمه‌ی تایید/رد دسته‌جمعی.
     */
    public function getFilteredQuestionsGrouped()
    {
        return $this->getFilteredQuestions()
            ->groupBy(fn($q) => ($q->chapter_id ?? '0').'-'.($q->section_id ?? '0'))
            ->map(function ($questions) {

                $first = $questions->first();

                return [
                    'chapter_id' => $first->chapter_id,
                    'chapter_title' => $first->chapter?->title,
                    'section_id' => $first->section_id,
                    'section_title' => $first->section?->title,
                    'questions' => $questions,
                    'pending_count' => $questions->where('status', 'pending')->count(),
                ];
            })
            ->values();
    }

    /**
     * تایید یا رد دسته‌جمعی همه‌ی سوالات «در انتظار بررسی» یک
     * زیرگروه (فصل/بخش) — فقط ادمین/سوپرادمین.
     */
    public function bulkReviewGroup(?int $chapterId, ?int $sectionId, string $decision): void
    {
        if (! $this->isReviewer()) {
            return;
        }

        $query = Question::where('created_by', $this->selectedCreatorId)
            ->where('status', 'pending')
            ->where('book_id', $this->selectedBookId);

        $chapterId
            ? $query->where('chapter_id', $chapterId)
            : $query->whereNull('chapter_id');

        $sectionId
            ? $query->where('section_id', $sectionId)
            : $query->whereNull('section_id');

        $count = $query->count();

        $query->update([

            'status' => $decision === 'approve' ? 'approved' : 'rejected',

            'reviewed_by' => auth()->id(),

        ]);

        Notification::make()
            ->title($count.' سوال '.($decision === 'approve' ? 'تأیید' : 'رد').' شد.')
            ->success()
            ->send();
    }

    /**
     * تایید یا رد یک سوال مشخص — فقط ادمین/سوپرادمین، فقط روی
     * سوالات «در انتظار بررسی».
     */
    public function reviewSingleQuestion(int $questionId, string $decision): void
    {
        if (! $this->isReviewer()) {
            return;
        }

        $question = Question::where('id', $questionId)
            ->where('status', 'pending')
            ->first();

        if (! $question) {
            return;
        }

        $question->update([

            'status' => $decision === 'approve' ? 'approved' : 'rejected',

            'reviewed_by' => auth()->id(),

        ]);

        Notification::make()
            ->title($decision === 'approve' ? 'سوال تأیید شد.' : 'سوال رد شد.')
            ->success()
            ->send();
    }

    public function backToGroups(): void
    {
        $this->viewLevel = 'groups';

        $this->selectedAppId = null;
        $this->selectedCreatorId = null;
        $this->selectedGradeId = null;
        $this->selectedBookId = null;
        $this->selectedExamLevel = null;
    }

    public function backToExamLevels(): void
    {
        $this->viewLevel = 'examLevels';

        $this->selectedExamLevel = null;
    }
}
