<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

/**
 * لیست بانک سوالات — به‌صورت سه‌سطحی (نه یک لیست تخت)
 * --------------------------------------------------------------------
 * سطح ۱: هر ردیف یک ترکیب «اپلیکیشن + ایجادکننده + پایه + کتاب»ست.
 * سطح ۲: بعد از انتخاب یکی از بالا، سه ردیف: آزمون‌های بخش /
 *        فصل / کل کتاب — هرکدام با تعداد سوالات همان دسته.
 * سطح ۳: لیست واقعی سوالات همان دسته، با امکان ویرایش.
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

    /**
     * کوئری پایه — همان محدودیتِ «معلم فقط سوالات خودش را می‌بیند»
     * که در Resource تعریف شده.
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
