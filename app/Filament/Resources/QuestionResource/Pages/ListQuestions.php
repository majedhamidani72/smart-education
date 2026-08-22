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

    /**
     * کدام زیرگروه‌های سطح ۳ الان باز هستند — چون <details> معمولی
     * بعد از هر wire:click (مثلاً تایید/رد یک سوال) خودش را می‌بندد،
     * این وضعیت را صریح خودمان نگه می‌داریم تا باز بماند.
     */
    public array $expandedGroups = [];

    public function toggleGroup(string $key): void
    {
        if (in_array($key, $this->expandedGroups)) {

            $this->expandedGroups = array_values(array_diff($this->expandedGroups, [$key]));

        } else {

            $this->expandedGroups[] = $key;
        }
    }

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

    /**
     * اگر از دکمه‌ی «ذخیره و خروج» توی «افزودن سریع سوال» به اینجا
     * برگشته باشیم، به‌جای اولین صفحه (لیست کتاب‌ها)، مستقیم به
     * همان بخش/فصل/کل‌کتابی که رویش کار می‌کردیم می‌رویم — اطلاعات
     * لازم از طریق پارامترهای آدرس می‌آید.
     */
    public function mount(): void
    {
        $bookId = request()->query('book_id');

        if (! $bookId) {
            return;
        }

        $book = \App\Models\Book::with('appGradeSubject')->find($bookId);

        if (! $book) {
            return;
        }

        $chapterId = request()->query('chapter_id');

        $sectionId = request()->query('section_id');

        // ایجادکننده‌ی این دسته، همیشه خودِ کاربر فعلی است — چون
        // این بازگشت همیشه از فرم «افزودن سریع سوال» خودِ همین
        // کاربر می‌آید.
        $this->selectedAppId = $book->appGradeSubject?->app_id;
        $this->selectedGradeId = $book->appGradeSubject?->grade_id;
        $this->selectedCreatorId = auth()->id();
        $this->selectedBookId = $book->id;

        if ($sectionId) {
            $this->selectedExamLevel = 'section';
        } elseif ($chapterId) {
            $this->selectedExamLevel = 'chapter';
        } else {
            $this->selectedExamLevel = 'book';
        }

        $this->viewLevel = 'list';
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
    /**
     * کوئری پایه — همان محدودیتِ «معلم فقط سوالات خودش را می‌بیند»
     * که در Resource تعریف شده، به‌علاوه‌ی این‌که ادمین/سوپرادمین
     * هیچ‌وقت سوالات «پیش‌نویس» (هنوز ارسال‌نشده) را نمی‌بینند —
     * تا زمانی که خودِ معلم دکمه‌ی «ارسال برای بررسی» را نزند،
     * این سوالات کاملاً برای بررسی‌کننده نامرئی می‌مانند.
     */
    protected function baseQuery()
    {
        $query = QuestionResource::getEloquentQuery();

        if ($this->isReviewer()) {
            $query->where('status', '!=', 'draft');
        }

        return $query;
    }

    /**
     * سطح ۱: ترکیب‌های یکتای اپلیکیشن/ایجادکننده/پایه/کتاب —
     * مستقیم از book_id خودِ سوال (نه از طریق محتوا).
     */
    public function getGroups()
    {
        $questions = $this->baseQuery()
            ->with(['creator', 'book.appGradeSubject.grade', 'book.appGradeSubject.app'])
            ->whereHas('book')
            ->get();

        return $questions
            ->filter(fn($q) => $q->book !== null)
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
                    'draft_count' => $group->where('status', 'draft')->count(),
                    // تفکیک «در انتظار بررسی» به تفکیک بخش/درس، فصل،
                    // و کل کتاب — تا همین‌جا (سطح ۱) هم مشخص باشد
                    // کدام دسته نیاز به بررسی دارد، بدون نیاز به
                    // وارد شدن به سطح بعدی.
                    'pending_section' => $group->where('status', 'pending')->whereNotNull('section_id')->count(),
                    'pending_chapter' => $group->where('status', 'pending')->filter(fn($q) => ! $q->section_id && $q->chapter_id)->count(),
                    'pending_book' => $group->where('status', 'pending')->whereNull('chapter_id')->count(),
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

            // تعداد «در انتظار بررسی» به تفکیک همین سه دسته — تا
            // همین‌جا (سطح ۲) هم مشخص باشد کدام کارت نیاز به
            // بررسی دارد.
            'pending_section' => $base()->where('status', 'pending')->whereNotNull('section_id')->count(),

            'pending_chapter' => $base()->where('status', 'pending')->whereNull('section_id')->whereNotNull('chapter_id')->count(),

            'pending_book' => $base()->where('status', 'pending')->whereNull('chapter_id')->count(),

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
                    'key' => ($first->chapter_id ?? '0').'-'.($first->section_id ?? '0'),
                    'chapter_id' => $first->chapter_id,
                    'chapter_title' => $first->chapter?->title,
                    'section_id' => $first->section_id,
                    'section_title' => $first->section?->title,
                    'questions' => $questions,
                    'pending_count' => $questions->where('status', 'pending')->count(),
                    'draft_count' => $questions->where('status', 'draft')->count(),
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
    /**
     * معلم، یک سوال «پیش‌نویس» خودش را برای بررسی ارسال می‌کند —
     * از این لحظه، ادمین/سوپرادمین می‌توانند ببینندش.
     */
    public function submitSingleForReview(int $questionId): void
    {
        $question = Question::where('id', $questionId)
            ->where('status', 'draft')
            ->where('created_by', auth()->id())
            ->first();

        if (! $question) {
            return;
        }

        $question->update(['status' => 'pending']);

        Notification::make()
            ->title('سوال برای بررسی ارسال شد.')
            ->success()
            ->send();
    }

    /**
     * ارسال دسته‌جمعی همه‌ی سوالات «پیش‌نویس» یک زیرگروه (فصل/
     * بخش) برای بررسی — فقط سوالات خودِ همین معلم.
     */
    public function submitGroupForReview(?int $chapterId, ?int $sectionId): void
    {
        $query = Question::where('created_by', auth()->id())
            ->where('status', 'draft')
            ->where('book_id', $this->selectedBookId);

        $chapterId
            ? $query->where('chapter_id', $chapterId)
            : $query->whereNull('chapter_id');

        $sectionId
            ? $query->where('section_id', $sectionId)
            : $query->whereNull('section_id');

        $count = $query->count();

        $query->update(['status' => 'pending']);

        Notification::make()
            ->title($count.' سوال برای بررسی ارسال شد.')
            ->success()
            ->send();
    }

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
