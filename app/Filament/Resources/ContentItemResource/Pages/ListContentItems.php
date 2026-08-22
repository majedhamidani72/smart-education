<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use App\Models\ContentItem;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

/**
 * لیست محتوای آموزشی — به‌صورت دوسطحی (نه یک لیست تخت)
 * --------------------------------------------------------------------
 * سطح ۱: هر کارت یک ترکیب «اپلیکیشن + ایجادکننده + پایه + کتاب»ست.
 * سطح ۲: بعد از انتخاب یک کارت، محتواها به تفکیک دقیق فصل/بخش
 *        گروه‌بندی و قابل‌جمع‌شدن نمایش داده می‌شوند — همراه با
 *        تایید/رد تکی یا دسته‌جمعی (ادمین/سوپرادمین) و ارسال
 *        برای بررسی تکی یا دسته‌جمعی (معلم).
 * --------------------------------------------------------------------
 * همان معماری «بانک سوالات» است، فقط بدون سطح میانیِ «بخش/فصل/کل
 * کتاب» — چون محتوا (برخلاف سوال) از اول مستقیم به یک فصل/بخش
 * مشخص وصل است، نیازی به آن سطح میانی نیست.
 */
class ListContentItems extends ListRecords
{
    protected static string $resource = ContentItemResource::class;

    protected static string $view = 'filament.resources.content-item-resource.pages.list-content-items';

    public string $viewLevel = 'groups';

    public ?int $selectedAppId = null;

    public ?int $selectedCreatorId = null;

    public ?int $selectedGradeId = null;

    public ?int $selectedBookId = null;

    public ?string $selectedContentTypeSlug = null;

    public array $expandedGroups = [];

    // برای فرم کوچیکِ «دلیل رد» که هنگام زدن دکمه‌ی «رد» (تکی یا
    // گروهی) به‌صورت اینلاین باز می‌شود.
    public ?int $rejectingItemId = null;

    public ?string $rejectingGroupKey = null;

    public string $rejectionReasonInput = '';

    protected function getHeaderActions(): array
    {
        return [

            \Filament\Actions\CreateAction::make()
                ->label('ایجاد محتوای آموزشی'),

        ];
    }

    /**
     * اگر از دکمه‌ی «بازگشت» توی صفحه‌ی «ایجاد محتوای آموزشی» به
     * اینجا برگشته باشیم، به‌جای اولین صفحه (لیست کتاب‌ها)، مستقیم
     * به همان فصل/بخش و نوع محتوایی که رویش کار می‌کردیم می‌رویم.
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

        $this->selectedAppId = $book->appGradeSubject?->app_id;
        $this->selectedGradeId = $book->appGradeSubject?->grade_id;
        $this->selectedCreatorId = auth()->id();
        $this->selectedBookId = $book->id;

        $contentTypeId = request()->query('content_type_id');

        if ($contentTypeId) {

            $this->selectedContentTypeSlug = \App\Models\ContentType::whereKey($contentTypeId)->value('slug');

            $this->viewLevel = 'list';

        } else {

            $this->viewLevel = 'contentTypes';
        }
    }

    public function toggleGroup(string $key): void
    {
        if (in_array($key, $this->expandedGroups)) {

            $this->expandedGroups = array_values(array_diff($this->expandedGroups, [$key]));

        } else {

            $this->expandedGroups[] = $key;
        }
    }

    protected function isReviewer(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('SuperAdmin') || $user?->hasRole('Admin');
    }

    /**
     * کوئری پایه — همان محدودیت «معلم فقط محتوای کتاب‌های خودش را
     * می‌بیند» که در Resource تعریف شده. ادمین/سوپرادمین هیچ‌وقت
     * محتوای «پیش‌نویس» (هنوز ارسال‌نشده) را نمی‌بینند.
     */
    protected function baseQuery()
    {
        $query = ContentItemResource::getEloquentQuery();

        if ($this->isReviewer()) {
            $query->where('status', '!=', 'draft');
        }

        return $query;
    }

    /**
     * سطح ۱: ترکیب‌های یکتای اپلیکیشن/ایجادکننده/پایه/کتاب.
     */
    public function getGroups()
    {
        $items = $this->baseQuery()
            ->whereHas('chapter.book')
            ->get();

        return $items
            ->filter(fn($i) => $i->chapter?->book !== null)
            ->groupBy(function ($i) {

                $book = $i->chapter->book;

                return $book->appGradeSubject?->app_id.'-'
                    .$i->created_by.'-'
                    .$book->appGradeSubject?->grade_id.'-'
                    .$book->id;
            })
            ->map(function ($group) {

                $first = $group->first();

                $book = $first->chapter->book;

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
        $this->viewLevel = 'contentTypes';
    }

    public function backToGroups(): void
    {
        $this->viewLevel = 'groups';

        $this->selectedAppId = null;
        $this->selectedCreatorId = null;
        $this->selectedGradeId = null;
        $this->selectedBookId = null;
        $this->selectedContentTypeSlug = null;
    }

    /**
     * مسیر فعلی برای نوار breadcrumb بالای صفحه — تا کاربر همیشه
     * دقیق بداند کجای اپلیکیشن/پایه/کتاب/نوع محتوا/فصل قرار دارد.
     */
    public function getBreadcrumbPath(): array
    {
        $path = [];

        if (! $this->selectedBookId) {
            return $path;
        }

        $book = \App\Models\Book::with('appGradeSubject.grade', 'appGradeSubject.app')->find($this->selectedBookId);

        if (! $book) {
            return $path;
        }

        $path[] = ['label' => $book->appGradeSubject?->app?->title, 'action' => null];

        $path[] = ['label' => 'پایه '.$book->appGradeSubject?->grade?->title, 'action' => null];

        $path[] = ['label' => $book->title, 'action' => 'backToContentTypes'];

        if ($this->selectedContentTypeSlug) {

            $typeLabel = match ($this->selectedContentTypeSlug) {
                'teaching' => '🎥 تدریس',
                'step_by_step' => '📝 گام به گام',
                'sample_questions' => '📄 نمونه سوالات',
                default => $this->selectedContentTypeSlug,
            };

            $path[] = ['label' => $typeLabel, 'action' => 'backToContentTypes'];
        }

        return $path;
    }

    /**
     * سطح ۲: تعداد محتوا به تفکیک نوع (تدریس/گام‌به‌گام/نمونه
     * سوالات) — چون هر کتاب معمولاً هر سه نوع را دارد و قاطی‌کردن
     * آن‌ها با هم، پیدا کردن هرکدام را سخت می‌کند.
     */
    public function getContentTypeCounts()
    {
        $base = fn() => $this->baseQuery()
            ->where('created_by', $this->selectedCreatorId)
            ->whereHas('chapter.book', fn($q) => $q->where('id', $this->selectedBookId));

        return \App\Models\ContentType::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($type) use ($base) {

                return [
                    'slug' => $type->slug,
                    'title' => $type->title,
                    'icon' => $type->icon,
                    'count' => (clone $base())->where('content_type_id', $type->id)->count(),
                    'pending_count' => (clone $base())->where('content_type_id', $type->id)->where('status', 'pending')->count(),
                    'draft_count' => (clone $base())->where('content_type_id', $type->id)->where('status', 'draft')->count(),
                ];
            });
    }

    public function selectContentType(string $slug): void
    {
        $this->selectedContentTypeSlug = $slug;
        $this->viewLevel = 'list';
    }

    public function backToContentTypes(): void
    {
        $this->viewLevel = 'contentTypes';
        $this->selectedContentTypeSlug = null;
    }

    public function getSelectedContentTypeId(): ?int
    {
        return \App\Models\ContentType::where('slug', $this->selectedContentTypeSlug)->value('id');
    }

    /**
     * سطح ۲: محتواهای همان کتاب، به تفکیک دقیق فصل/بخش گروه‌بندی
     * شده.
     */
    public function getGroupedItems()
    {
        $contentType = \App\Models\ContentType::where('slug', $this->selectedContentTypeSlug)->first();

        return $this->baseQuery()
            ->where('created_by', $this->selectedCreatorId)
            ->whereHas('chapter.book', fn($q) => $q->where('id', $this->selectedBookId))
            ->when($contentType, fn($q) => $q->where('content_type_id', $contentType->id))
            ->with(['section', 'chapter', 'contentType'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn($i) => $i->chapter_id.'-'.($i->section_id ?? '0'))
            ->map(function ($items) {

                $first = $items->first();

                // ترتیب نمایش ثابت: تدریس، گام‌به‌گام، نمونه سوالات،
                // و در آخر هرچیز دیگری — تا معلم/ادمین همیشه بداند
                // کجا دنبال چه نوع محتوایی بگردد.
                $typeOrder = ['teaching' => 1, 'step_by_step' => 2, 'sample_questions' => 3];

                $typeMeta = [
                    'teaching' => ['label' => 'تدریس', 'icon' => '🎥', 'accent' => 'rgb(37,99,235)'],
                    'step_by_step' => ['label' => 'گام به گام', 'icon' => '📝', 'accent' => 'rgb(21,128,61)'],
                    'sample_questions' => ['label' => 'نمونه سوالات', 'icon' => '📄', 'accent' => 'rgb(194,65,12)'],
                ];

                $byType = $items
                    ->groupBy(fn($i) => $i->contentType?->slug ?? 'other')
                    ->sortBy(fn($group, $slug) => $typeOrder[$slug] ?? 99)
                    ->map(function ($typeItems, $slug) use ($typeMeta) {

                        return [
                            'slug' => $slug,
                            'label' => $typeMeta[$slug]['label'] ?? ($typeItems->first()->contentType?->title ?? 'سایر'),
                            'icon' => $typeMeta[$slug]['icon'] ?? '📦',
                            'accent' => $typeMeta[$slug]['accent'] ?? 'rgb(100,116,139)',
                            'items' => $typeItems,
                        ];
                    })
                    ->values();

                return [
                    'key' => $first->chapter_id.'-'.($first->section_id ?? '0'),
                    'chapter_id' => $first->chapter_id,
                    'chapter_title' => $first->chapter?->title,
                    'section_id' => $first->section_id,
                    'section_title' => $first->section?->title,
                    'items' => $items,
                    'by_type' => $byType,
                    'pending_count' => $items->where('status', 'pending')->count(),
                    'draft_count' => $items->where('status', 'draft')->count(),
                ];
            })
            ->values();
    }

    public function startRejectItem(int $itemId): void
    {
        $this->rejectingItemId = $itemId;
        $this->rejectingGroupKey = null;
        $this->rejectionReasonInput = '';
    }

    public function startRejectGroup(string $groupKey): void
    {
        $this->rejectingGroupKey = $groupKey;
        $this->rejectingItemId = null;
        $this->rejectionReasonInput = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingItemId = null;
        $this->rejectingGroupKey = null;
        $this->rejectionReasonInput = '';
    }

    public function confirmRejectItem(): void
    {
        if (! $this->rejectingItemId || blank($this->rejectionReasonInput)) {
            return;
        }

        $this->reviewSingleItem($this->rejectingItemId, 'reject', $this->rejectionReasonInput);

        $this->cancelReject();
    }

    public function confirmRejectGroup(int $chapterId, ?int $sectionId): void
    {
        if (blank($this->rejectionReasonInput)) {
            return;
        }

        $this->rejectGroup($chapterId, $sectionId, $this->rejectionReasonInput);

        $this->cancelReject();
    }

    /**
     * معلم، یک محتوای «پیش‌نویس» خودش را برای بررسی ارسال می‌کند.
     */
    public function submitSingleForReview(int $itemId): void
    {
        $item = ContentItem::where('id', $itemId)
            ->where('status', 'draft')
            ->where('created_by', auth()->id())
            ->first();

        if (! $item) {
            return;
        }

        $item->update(['status' => 'pending']);

        Notification::make()
            ->title('محتوا برای بررسی ارسال شد.')
            ->success()
            ->send();
    }

    /**
     * ارسال دسته‌جمعی همه‌ی محتوای «پیش‌نویس» یک زیرگروه (فصل/بخش).
     */
    public function submitGroupForReview(int $chapterId, ?int $sectionId): void
    {
        $query = ContentItem::where('created_by', auth()->id())
            ->where('status', 'draft')
            ->where('chapter_id', $chapterId);

        $sectionId
            ? $query->where('section_id', $sectionId)
            : $query->whereNull('section_id');

        $count = $query->count();

        $query->update(['status' => 'pending']);

        Notification::make()
            ->title($count.' محتوا برای بررسی ارسال شد.')
            ->success()
            ->send();
    }

    /**
     * تایید یا رد یک محتوای مشخص — فقط ادمین/سوپرادمین.
     */
    public function reviewSingleItem(int $itemId, string $decision, ?string $reason = null): void
    {
        if (! $this->isReviewer()) {
            return;
        }

        $item = ContentItem::where('id', $itemId)
            ->where('status', 'pending')
            ->first();

        if (! $item) {
            return;
        }

        $item->update([

            'status' => $decision === 'approve' ? 'approved' : 'rejected',

            'rejection_reason' => $decision === 'reject' ? $reason : null,

            'reviewed_by' => auth()->id(),

            'reviewed_at' => now(),

        ]);

        Notification::make()
            ->title($decision === 'approve' ? 'محتوا تأیید شد.' : 'محتوا رد شد.')
            ->success()
            ->send();
    }

    /**
     * تایید دسته‌جمعی — فقط ادمین/سوپرادمین. رد دسته‌جمعی چون
     * نیاز به یک دلیل واحد دارد، از طریق مودال جدا (rejectGroup)
     * انجام می‌شود.
     */
    public function approveGroup(int $chapterId, ?int $sectionId): void
    {
        if (! $this->isReviewer()) {
            return;
        }

        $query = ContentItem::where('status', 'pending')
            ->where('chapter_id', $chapterId);

        $sectionId
            ? $query->where('section_id', $sectionId)
            : $query->whereNull('section_id');

        $count = $query->count();

        $query->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Notification::make()
            ->title($count.' محتوا تأیید شد.')
            ->success()
            ->send();
    }

    public function rejectGroup(int $chapterId, ?int $sectionId, string $reason): void
    {
        if (! $this->isReviewer()) {
            return;
        }

        $query = ContentItem::where('status', 'pending')
            ->where('chapter_id', $chapterId);

        $sectionId
            ? $query->where('section_id', $sectionId)
            : $query->whereNull('section_id');

        $count = $query->count();

        $query->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Notification::make()
            ->title($count.' محتوا رد شد.')
            ->success()
            ->send();
    }
}
