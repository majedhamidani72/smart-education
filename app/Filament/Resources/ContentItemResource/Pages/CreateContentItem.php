<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use App\Models\ContentItem;
use App\Models\ContentType;
use App\Models\PdfFile;
use App\Models\StepByStep;
use App\Models\StepByStepPage;
use App\Models\Video;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateContentItem extends CreateRecord
{
    protected static string $resource = ContentItemResource::class;

    // یک دکمه‌ی «بازگشت» بالای صفحه (کنار عنوان) — چون فرم طولانی
    // است و دکمه‌ی «لغو» ته فرم، بدون اسکرول کامل به چشم نمی‌آید.
    protected function getHeaderActions(): array
    {
        return [

            \Filament\Actions\Action::make('back')
                ->label('بازگشت')
                ->icon('heroicon-o-arrow-right')
                ->color('gray')
                ->url(static::getResource()::getUrl('index', array_filter([
                    'book_id' => request()->query('book_id'),
                    'chapter_id' => request()->query('chapter_id'),
                    'section_id' => request()->query('section_id'),
                    'content_type_id' => request()->query('content_type_id'),
                ]))),

        ];
    }

    /**
     * اگر از دکمه‌ی «ادامه‌ی ایجاد» توی «محتوای آموزشی» به اینجا
     * آمده باشیم، مسیر آموزشی و نوع محتوا از طریق پارامترهای
     * آدرس از قبل پر می‌شوند — دیگر لازم نیست دوباره اپ/پایه/
     * درس/کتاب/فصل/بخش/نوع محتوا را انتخاب کنی.
     */
    public function mount(): void
    {
        parent::mount();

        $bookId = request()->query('book_id');

        if (! $bookId) {
            return;
        }

        $book = \App\Models\Book::with('appGradeSubject')->find($bookId);

        if (! $book) {
            return;
        }

        $this->form->fill(array_filter([

            'app_id' => $book->appGradeSubject?->app_id,

            'grade_id' => $book->appGradeSubject?->grade_id,

            'subject_id' => $book->appGradeSubject?->subject_id,

            'book_id' => $book->id,

            'chapter_id' => request()->query('chapter_id'),

            'section_id' => request()->query('section_id'),

            'content_type_id' => request()->query('content_type_id'),

        ], fn($value) => $value !== null));
    }

    // فقط یک دکمه‌ی «ایجاد» باقی می‌ماند (بدون گزینه‌ی «ایجاد و
    // افزودن دیگر») تا همیشه، بدون استثنا، بعد از ثبت محتوا به
    // لیست برگردد.
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        // معلم نمی‌تواند وضعیت را خودش تعیین کند — همیشه با
        // «پیش‌نویس» شروع می‌شود، تا خودش با دکمه‌ی «ارسال برای
        // بررسی» (تکی یا دسته‌جمعی) آن را به دست ادمین/سوپرادمین
        // برساند — دقیقاً مثل بانک سوالات. ادمین/سوپرادمین می‌توانند
        // مستقیم محتوای «در انتظار بررسی» بسازند.
        $isReviewer = auth()->user()?->hasRole('SuperAdmin') || auth()->user()?->hasRole('Admin');

        $data['status'] = $isReviewer ? 'pending' : 'draft';

        // عنوان نهایی محتوا از روی همان فیلد اختصاصی نوع محتوا
        // ساخته می‌شود (دیگر فیلد «عنوان» جداگانه‌ای در فرم
        // وجود ندارد؛ نگاه کنید به ContentItemResource::form).
        $title = $this->resolveTitle($data);

        $data['title'] = $title;

        $data['slug'] = $this->uniqueSlug(
            filled($title) ? Str::slug($title) : Str::random(10),
            $data['section_id'] ?? null
        );

        return $data;
    }

    /**
     * یک اسلاگ یکتا برای همین «بخش» می‌سازد.
     * --------------------------------------------------------------------
     * یکتایی محتوا در دیتابیس بر اساس ترکیب (section_id, slug) است.
     * اگر معلم/ادمین دو محتوای متفاوت را با عنوان یکسان در همان
     * بخش بسازد (مثلاً هم یک ویدئو هم یک PDF به اسم «کاردرکلاس»)،
     * به‌جای خطای یکتایی، به انتهای اسلاگ یک شماره اضافه می‌شود
     * (kardrklas-2, kardrklas-3, ...) تا تداخل پیش نیاید.
     */
    protected function uniqueSlug(string $baseSlug, ?int $sectionId, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;

        $counter = 2;

        while (
            ContentItem::query()
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

    /**
     * بر اساس نوع محتوای انتخاب‌شده، عنوان را از فیلد اختصاصی
     * همان نوع می‌خواند:
     * تدریس → عنوان ویدئو، گام‌به‌گام → عنوان اولین صفحه،
     * نمونه سوالات → عنوان فایل PDF.
     */
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

    protected function afterCreate(): void
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

                $videoPath = data_get($this->data, 'video.video_file');

                if (
                    filled(data_get($this->data, 'video.title')) ||
                    filled($videoPath)
                ) {

                    Video::create(array_merge([

                        'content_item_id' => $record->id,

                        'uploaded_by' => auth()->id(),

                    ], $this->extractFileMeta($videoPath)));
                }

                break;

            /*
            |--------------------------------------------------------------------------
            | گام به گام
            |--------------------------------------------------------------------------
            */

            case 'step_by_step':

                $pages = data_get($this->data, 'stepByStep', []);

                // رکورد step_by_steps صرفاً یک «ظرف» برای صفحات است
                // و خودش فایل مستقلی ندارد، ولی ستون‌های اطلاعات
                // فایل روی جدولش اجباری‌اند؛ برای همین با مقادیر
                // خلاصه (نه یک فایل واقعی) پر می‌شوند. حجم کل هم
                // مجموع حجم تصاویر همه‌ی صفحات است.
                $step = StepByStep::create([

                    'content_item_id' => $record->id,

                    'uploaded_by' => auth()->id(),

                    'directory' => 'step-by-step',

                    'filename' => 'content-'.$record->id,

                    'original_name' => 'content-'.$record->id,

                    'extension' => '',

                    'mime_type' => 'application/octet-stream',

                    'file_size' => $this->sumImageSizes($pages),

                ]);

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

                $pdfPath = data_get($this->data, 'pdfFile.file');

                if (
                    filled(data_get($this->data, 'pdfFile.title')) ||
                    filled($pdfPath)
                ) {

                    PdfFile::create(array_merge([

                        'content_item_id' => $record->id,

                        'uploaded_by' => auth()->id(),

                    ], $this->extractFileMeta($pdfPath)));
                }

                break;
        }
    }

    /**
     * از روی مسیر فایلی که Filament ذخیره کرده (روی دیسک public)،
     * ستون‌های اجباری جدول‌های videos و pdf_files را می‌سازد:
     * پوشه، نام فایل، پسوند، نوع MIME و حجم فایل. این ستون‌ها به
     * FileUpload خودِ فرم داده نمی‌شوند (Filament فقط مسیر ذخیره‌
     * شده را برمی‌گرداند)، برای همین باید اینجا از روی فایل واقعی
     * روی دیسک استخراج شوند.
     */
    protected function extractFileMeta(string|array|null $path): array
    {
        // FileUpload گاهی (بسته به مرحله‌ی پردازش Livewire) به‌جای
        // یک رشته، آرایه‌ای شامل مسیر فایل برمی‌گرداند. اینجا در
        // هر دو حالت، مسیر واقعی (رشته) را استخراج می‌کنیم.
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

            // توجه: Filament به‌صورت پیش‌فرض نام فایل را با یک
            // شناسه‌ی تصادفی جایگزین می‌کند تا از تداخل نام‌ها
            // جلوگیری شود؛ یعنی نام اصلی فایلی که معلم آپلود کرده
            // اینجا در دسترس نیست. به‌جای آن، همان نام ذخیره‌شده
            // به‌عنوان original_name هم استفاده می‌شود.
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
     * مجموع حجم تصاویر همه‌ی صفحات گام‌به‌گام را حساب می‌کند
     * (برای پر کردن ستون file_size رکورد «ظرف» step_by_steps).
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

    protected function getRedirectUrl(): string
    {
        // همیشه به لیست محتوای آموزشی برمی‌گردد — چه سوپرادمین/
        // ادمین باشد چه معلم. قبلاً تلاش می‌شد به آدرس دقیق صفحه‌ی
        // قبلی (previousUrl) برگردد که همیشه قابل اعتماد نبود؛
        // ساده و تضمین‌شده‌تر این است که همیشه همین لیست باز شود.
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'محتوای آموزشی با موفقیت ایجاد شد.';
    }
}
