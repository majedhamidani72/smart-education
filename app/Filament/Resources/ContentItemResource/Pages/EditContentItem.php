<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use App\Filament\Resources\ContentItemResource\Pages\Concerns\HandlesMissingTemporaryUploads;
use App\Models\ContentType;
use App\Models\PdfFile;
use App\Models\StepByStep;
use App\Models\StepByStepPage;
use App\Models\Video;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditContentItem extends EditRecord
{
    use HandlesMissingTemporaryUploads;

    protected static string $resource = ContentItemResource::class;

    public function mount(int|string $record): void
    {
        try {
            parent::mount($record);
        } catch (ModelNotFoundException) {
            // اگر کاربر با Back مرورگر به صفحه ویرایش رکوردی برگردد
            // که قبلاً حذف دائمی شده، به‌جای 404 به همان فهرست
            // فیلترشده هدایت می‌شود.
            $this->redirect(static::getResource()::getUrl('index', array_filter([
                'book_id' => request()->query('book_id'),
                'chapter_id' => request()->query('chapter_id'),
                'section_id' => request()->query('section_id'),
                'content_type_id' => request()->query('content_type_id'),
                'creator_id' => request()->query('creator_id'),
            ])));
        }
    }

    /**
     * وقتی محتوا «در انتظار بررسی» است، معلم فقط می‌تواند ببیندش
     * (فرم غیرفعال/فقط‌خواندنی) — تا ادمین/سوپرادمین تصمیم بگیرد.
     * به‌محض رد شدن، دوباره قابل ویرایش می‌شود.
     */
    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        $form = parent::form($form);

        $isReviewer = auth()->user()?->hasRole('SuperAdmin')
            || auth()->user()?->hasRole('Admin');

        if ($this->record->status === 'pending' && ! $isReviewer) {

            $form = $form->disabled();
        }

        return $form;
    }

    protected function getFormActions(): array
    {
        $isReviewer = auth()->user()?->hasRole('SuperAdmin')
            || auth()->user()?->hasRole('Admin');

        if ($this->record->status === 'pending' && ! $isReviewer) {
            return [];
        }

        return parent::getFormActions();
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->url(fn (): string => $this->previousPageUrl());
    }

    /**
     * پر کردن فرم ویرایش با اطلاعات واقعی رکورد.
     * --------------------------------------------------------------------
     * چند دسته فیلد در فرم اصلاً ستون مستقیم روی content_items
     * نیستند و Filament نمی‌تواند خودکار پرشان کند؛ اینجا دستی
     * از روی رابطه‌های واقعی رکورد بازسازی می‌شوند:
     *
     * ۱) اپلیکیشن/پایه/درس/کتاب/فصل — این‌ها فقط برای فیلتر کردن
     *    گزینه‌ها هستند و ذخیره نمی‌شوند؛ روی EDIT باید از روی
     *    section_id واقعیِ رکورد به عقب بازسازی شوند.
     * ۲) عنوان ویدئو/PDF — چون جدول‌های videos و pdf_files ستون
     *    title ندارند (عنوان واقعی روی خودِ content_items.title
     *    است)، همان مقدار به فیلد نمایشی مربوطه کپی می‌شود.
     * ۳) فایل فعلی ویدئو/PDF — تا در FileUpload به‌عنوان «فایل
     *    فعلی» نمایش داده شود (وگرنه انگار هیچ فایلی وجود ندارد).
     * ۴) صفحات گام‌به‌گام — از روی رابطه‌ی stepByStep.pages.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\ContentItem $record */
        $record = $this->record;

        // ۱) بازسازی مسیر آموزشی از روی chapter_id (مستقل از
        // section_id، چون بخش اختیاری است ولی فصل همیشه ذخیره
        // می‌شود).
        $chapter = $record->chapter()
            ->with('book.appGradeSubject')
            ->first();

        if ($chapter && $chapter->book) {

            $book = $chapter->book;

            $data['chapter_id'] = $chapter->id;

            $data['book_id'] = $book->id;

            $data['subject_id'] = $book->appGradeSubject?->subject_id;

            $data['grade_id'] = $book->appGradeSubject?->grade_id;

            $data['app_id'] = $book->appGradeSubject?->app_id;
        }

        // ۲ و ۳) عنوان و فایل فعلی، بسته به نوع محتوا
        $typeSlug = ContentType::find($record->content_type_id)?->slug;

        if ($typeSlug === 'teaching') {

            $data['video'] = [
                'title' => $record->title,
                'video_file' => $record->video
                    ? trim($record->video->directory.'/'.$record->video->filename, '/')
                    : null,
            ];

        } elseif ($typeSlug === 'sample_questions') {

            $data['pdfFile'] = [
                'title' => $record->title,
                'file' => $record->pdfFile
                    ? trim($record->pdfFile->directory.'/'.$record->pdfFile->filename, '/')
                    : null,
            ];

        } elseif ($typeSlug === 'step_by_step') {

            // ۴) صفحات گام‌به‌گام
            // --------------------------------------------------------------
            // Repeater در Filament هر آیتم را با یک شناسه‌ی یکتا
            // (UUID) کلیدگذاری می‌کند، نه با شماره‌ی ساده؛ اگر این
            // ساختار رعایت نشود، Livewire نمی‌تواند فایل‌های
            // آپلودشده‌ی هر آیتم را درست رندر کند و عکس خالی
            // نمایش داده می‌شود.
            $data['stepByStep'] = $record->stepByStep
                ?->pages()
                ->orderBy('sort_order')
                ->get()
                ->mapWithKeys(fn($page) => [

                    (string) Str::uuid() => [

                        'title' => $page->title,

                        'image' => $page->image,

                        'sort_order' => $page->sort_order,

                    ],

                ])
                ->toArray() ?? [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // «ایجادکننده» هرگز نباید با ویرایش عوض شود — این فیلد
        // فقط یک‌بار، همان لحظه‌ی ساخت اولیه‌ی محتوا مشخص می‌شود
        // (نگاه کنید به CreateContentItem). این خط صراحتاً از هر
        // احتمال رونویسی‌شدنِ آن هنگام ذخیره‌ی ویرایش جلوگیری
        // می‌کند.
        unset($data['created_by']);

        // محافظت سمت سرور: حتی اگر فرم در رابط کاربری این بخش را
        // از معلم مخفی می‌کند، تغییر وضعیت (تأیید/رد/انتشار) فقط
        // باید توسط ادمین یا سوپرادمین ثبت شود — نه با یک درخواست
        // دستکاری‌شده از سمت معلم.
        $isReviewer = auth()->user()?->hasRole('Admin')
            || auth()->user()?->hasRole('SuperAdmin');

        if (! $isReviewer) {

            if ($this->record->status === 'rejected') {

                // معلم دارد محتوای ردشده را اصلاح می‌کند؛ دیگر
                // خودکار به «در انتظار بررسی» برنمی‌گردد — به
                // «پیش‌نویس» می‌رود تا خودِ معلم با زدن دکمه‌ی
                // «ارسال برای بررسی»، دوباره و آگاهانه بفرستدش.
                $data['status'] = 'draft';

                $data['rejection_reason'] = null;

                $data['reviewed_by'] = null;

                $data['reviewed_at'] = null;

            } else {

                // در غیر این صورت، معلم عملاً نمی‌تواند وضعیت را از
                // این مسیر تغییر دهد.
                $data['status'] = $this->record->status;

                unset($data['reviewed_by'], $data['reviewed_at'], $data['rejection_reason']);
            }

        } elseif (
            isset($data['status']) &&
            in_array(
                $data['status'],
                [
                    'approved',
                    'published',
                ],
                true
            )
        ) {
            $data['reviewed_by'] = auth()->id();

            $data['reviewed_at'] = now();
        }

        // عنوان نهایی محتوا از روی همان فیلد اختصاصی نوع محتوا
        // بازسازی می‌شود (همان منطق CreateContentItem).
        $title = $this->resolveTitle($data);

        if (filled($title)) {

            $data['title'] = $title;

            $data['slug'] = $this->uniqueSlug(
                Str::slug($title),
                $data['section_id'] ?? $this->record->section_id,
                $this->record->id
            );
        }

        return $data;
    }

    /**
     * یک اسلاگ یکتا برای همین «بخش» می‌سازد (همان منطق
     * CreateContentItem::uniqueSlug، با این تفاوت که رکورد خودِ
     * این محتوا از بررسی تکراری بودن کنار گذاشته می‌شود — وگرنه
     * ویرایش یک محتوای موجود همیشه با خودش تداخل می‌کرد).
     */
    protected function uniqueSlug(string $baseSlug, ?int $sectionId, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;

        $counter = 2;

        while (
            \App\Models\ContentItem::query()
                // The database unique index also includes soft-deleted rows.
                // Include them here so restoring/editing titles stays safe.
                ->withTrashed()
                ->where('section_id', $sectionId)
                ->where('slug', $slug)
                ->when($ignoreId, fn($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;

            $counter++;
        }

        return $slug;
    }

    protected function resolveTitle(array $data): ?string
    {
        $slug = ContentType::query()
            ->whereKey($data['content_type_id'] ?? null)
            ->value('slug');

        return match ($slug) {

            'teaching' => data_get($data, 'video.title'),

            'step_by_step' => collect(data_get($data, 'stepByStep', []))
                ->first()['title'] ?? null,

            'sample_questions' => data_get($data, 'pdfFile.title'),

            default => null,
        };
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        $type = ContentType::find(
            $record->content_type_id
        );

        if (! $type) {
            return;
        }

        switch ($type->slug) {

            /*
            |--------------------------------------------------------------------------
            | تدریس
            |--------------------------------------------------------------------------
            */

            case 'teaching':

                Video::updateOrCreate(

                    [
                        'content_item_id' => $record->id,
                    ],

                    array_merge(
                        [
                            'uploaded_by' => $record->video?->uploaded_by ?? auth()->id(),
                        ],
                        $this->extractFileMeta(
                            data_get($this->data, 'video.video_file')
                        )
                    )

                );

                break;

            /*
            |--------------------------------------------------------------------------
            | گام به گام
            |--------------------------------------------------------------------------
            */

            case 'step_by_step':

                $pages = data_get($this->data, 'stepByStep', []);

                $step = StepByStep::updateOrCreate(

                    [
                        'content_item_id' => $record->id,
                    ],

                    [
                        'uploaded_by' => $record->stepByStep?->uploaded_by ?? auth()->id(),

                        'directory' => 'step-by-step',

                        'filename' => 'content-'.$record->id,

                        'original_name' => 'content-'.$record->id,

                        'extension' => '',

                        'mime_type' => 'application/octet-stream',

                        'file_size' => $this->sumImageSizes($pages),
                    ]

                );

                $step->pages()->delete();

                foreach ($pages as $page) {

                    StepByStepPage::create([

                        'step_by_step_id' => $step->id,

                        'title' => $page['title'] ?? null,

                        'page_number' => $page['sort_order'] ?? 1,

                        'image' => is_array($page['image'] ?? null)
                            ? collect($page['image'])->first()
                            : ($page['image'] ?? null),

                        'sort_order' => $page['sort_order'] ?? 1,

                        'is_free' => false,

                    ]);
                }

                break;

            /*
            |--------------------------------------------------------------------------
            | نمونه سوال
            |--------------------------------------------------------------------------
            */

            case 'sample_questions':

                PdfFile::updateOrCreate(

                    [
                        'content_item_id' => $record->id,
                    ],

                    array_merge(
                        [
                            'uploaded_by' => $record->pdfFile?->uploaded_by ?? auth()->id(),
                        ],
                        $this->extractFileMeta(
                            data_get($this->data, 'pdfFile.file')
                        )
                    )

                );

                break;
        }
    }

    /**
     * از روی مسیر فایلی که Filament ذخیره کرده (روی دیسک public)،
     * ستون‌های اجباری جدول‌های videos و pdf_files را می‌سازد.
     * همان منطق CreateContentItem::extractFileMeta.
     */
    /**
     * مجموع حجم تصاویر همه‌ی صفحات گام‌به‌گام (همان منطق
     * CreateContentItem::sumImageSizes).
     */
    protected function sumImageSizes(array $pages): int
    {
        $disk = Storage::disk('public');

        return collect($pages)->sum(function ($page) use ($disk) {

            $imagePath = is_array($page['image'] ?? null)
                ? collect($page['image'])->first()
                : ($page['image'] ?? null);

            return ($imagePath && $disk->exists($imagePath))
                ? $disk->size($imagePath)
                : 0;
        });
    }

    protected function extractFileMeta(string|array|null $path): array
    {
        if (is_array($path)) {
            $path = collect($path)->first();
        }

        if (blank($path)) {

            return [
                'directory' => '',
                'filename' => '',
                'original_name' => '',
                'extension' => '',
                'mime_type' => 'application/octet-stream',
                'file_size' => 0,
            ];
        }

        $disk = Storage::disk('public');

        $directory = dirname($path);

        $filename = basename($path);

        return [

            'directory' => $directory === '.' ? '' : $directory,

            'filename' => $filename,

            'original_name' => $filename,

            'extension' => pathinfo($path, PATHINFO_EXTENSION) ?: '',

            'mime_type' => $disk->exists($path)
                ? ($disk->mimeType($path) ?: 'application/octet-stream')
                : 'application/octet-stream',

            'file_size' => $disk->exists($path)
                ? $disk->size($path)
                : 0,

        ];
    }

    /**
     * نوار مسیر بالای صفحه — از روی مسیر واقعی خودِ محتوا محاسبه
     * می‌شود تا حتی اگر مستقیم وارد این صفحه شده باشی، درست باشد.
     */
    public function getSubheading(): ?string
    {
        $chapter = $this->record->chapter()
            ->with('book.appGradeSubject.grade', 'book.appGradeSubject.app')
            ->first();

        $book = $chapter?->book;

        if (! $book) {
            return null;
        }

        $section = $this->record->section?->title;

        $path = collect([
            $book->appGradeSubject?->app?->title,
            'پایه '.$book->appGradeSubject?->grade?->title,
            $book->title,
            $chapter->title,
            $section,
            $this->record->contentType?->title,
        ])->filter()->implode(' ← ');

        return '📍 مسیر: '.$path;
    }

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('back')
                ->label('بازگشت')
                ->icon('heroicon-o-arrow-right')
                ->color('gray')
                ->url(fn (): string => $this->previousPageUrl()),

        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousPageUrl();
    }

    protected function previousPageUrl(): string
    {
        return static::getResource()::getUrl('index', array_filter([
            'book_id' => request()->query('book_id', $this->record->book_id),
            'chapter_id' => request()->query('chapter_id', $this->record->chapter_id),
            'section_id' => request()->query('section_id', $this->record->section_id),
            'content_type_id' => request()->query('content_type_id', $this->record->content_type_id),
            'creator_id' => request()->query('creator_id', $this->record->created_by),
        ], fn($value) => $value !== null && $value !== ''));
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'محتوای آموزشی با موفقیت ویرایش شد.';
    }
}
