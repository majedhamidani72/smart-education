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
 *        برای ادمین/سوپرادمین، سوالاتِ «پیش‌نویس» (که هنوز معلم
 *        ارسال نکرده) اصلاً دیده نمی‌شود، و هر زیرگروه یک دکمه‌ی
 *        تایید/رد دسته‌جمعی دارد.
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
     * که در Resource تعریف شده. توجه: برخلاف نسخه‌ی قبلی، اینجا
     * دیگر چیزی مخفی نمی‌شود — سوالات پیش‌نویس همچنان برای ادمین/
     * سوپرادمین دیده می‌شوند (شفافیت کامل)، فقط دکمه‌های تایید/رد
     * روی آن‌ها ظاهر نمی‌شوند (چون هنوز ارسال نشده‌اند).
     */
    protected function baseQuery()
    {
        return QuestionResource::getEloquentQuery();
    }

    /**
     * سطح ۱: ترکیب‌های یکتای اپلیکیشن/ایجادکننده/پایه/کتاب.
     */
    public function getGroups()
    {
        $questions = $this->baseQuery()
            ->with(['creator', 'contentItem.chapter.book.appGradeSubject.grade', 'contentItem.chapter.book.appGradeSubject.app'])
            ->get();

        return $questions
            ->filter(fn($q) => $q->contentItem?->chapter?->book)
            ->groupBy(function ($q) {

                $book = $q->contentItem->chapter->book;

                return $book->appGradeSubject?->app_id.'-'
                    .$q->created_by.'-'
                    .$book->appGradeSubject?->grade_id.'-'
                    .$book->id;
            })
            ->map(function ($group) {

                $first = $group->first();

                $book = $first->contentItem->chapter->book;

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
     * سطح ۲: تعداد سوالات به تفکیک «بخش» (content_item با section)،
     * «فصل» (content_item مستقیم زیر فصل، بدون بخش)، و «کل کتاب».
     * نکته‌ی صادقانه: چون سوال فقط به یک content_item وصل می‌شود
     * (که همیشه به یک فصل مشخص تعلق دارد)، امکان تفکیک واقعی
     * «سوال برای کل کتاب» با ساختار فعلی دیتابیس وجود ندارد —
     * این ردیف فعلاً همیشه صفر نمایش داده می‌شود.
     */
    public function getExamLevelCounts(): array
    {
        $questions = $this->baseQuery()
            ->where('created_by', $this->selectedCreatorId)
            ->whereHas('contentItem.chapter.book', fn($q) => $q->where('id', $this->selectedBookId))
            ->with('contentItem')
            ->get();

        return [

            'section' => $questions->filter(fn($q) => $q->contentItem?->section_id)->count(),

            'chapter' => $questions->filter(fn($q) => $q->contentItem && ! $q->contentItem->section_id)->count(),

            'book' => 0,

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
            ->whereHas('contentItem.chapter.book', fn($q) => $q->where('id', $this->selectedBookId))
            ->with(['contentItem.section', 'contentItem.chapter']);

        if ($this->selectedExamLevel === 'section') {
            $query->whereHas('contentItem', fn($q) => $q->whereNotNull('section_id'));
        } elseif ($this->selectedExamLevel === 'chapter') {
            $query->whereHas('contentItem', fn($q) => $q->whereNull('section_id'));
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * همان لیست سطح ۳، ولی این‌بار به تفکیک فصل/بخش دقیق گروه‌بندی
     * شده — هر زیرگروه دکمه‌ی «ادامه‌ی افزودن سوال» مخصوص به خودش
     * را دارد (با کتاب+فصل+بخش دقیقاً همان زیرگروه، از قبل پرشده)،
     * و برای ادمین/سوپرادمین، دکمه‌ی تایید/رد دسته‌جمعی هم دارد.
     */
    public function getFilteredQuestionsGrouped()
    {
        return $this->getFilteredQuestions()
            ->groupBy(fn($q) => $q->contentItem->chapter_id.'-'.($q->contentItem->section_id ?? '0'))
            ->map(function ($questions) {

                $first = $questions->first();

                return [
                    'key' => $first->contentItem->chapter_id.'-'.($first->contentItem->section_id ?? '0'),
                    'chapter_id' => $first->contentItem->chapter_id,
                    'chapter_title' => $first->contentItem->chapter?->title,
                    'section_id' => $first->contentItem->section_id,
                    'section_title' => $first->contentItem->section?->title,
                    'questions' => $questions,
                    'pending_count' => $questions->where('status', 'pending')->count(),
                ];
            })
            ->values();
    }

    /**
     * تایید یا رد دسته‌جمعی همه‌ی سوالات «در انتظار بررسی» یک
     * زیرگروه (فصل/بخش) — فقط ادمین/سوپرادمین. سوالات پیش‌نویس یا
     * قبلاً بررسی‌شده دست‌نخورده می‌مانند.
     */
    public function bulkReviewGroup(int $chapterId, ?int $sectionId, string $decision): void
    {
        if (! $this->isReviewer()) {
            return;
        }

        $query = Question::where('created_by', $this->selectedCreatorId)
            ->where('status', 'pending')
            ->whereHas('contentItem', function ($q) use ($chapterId, $sectionId) {

                $q->where('chapter_id', $chapterId);

                $sectionId
                    ? $q->where('section_id', $sectionId)
                    : $q->whereNull('section_id');
            });

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
