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
use Illuminate\Support\Str;

class CreateContentItem extends CreateRecord
{
    protected static string $resource = ContentItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

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

                if (
                    filled(data_get($this->data, 'video.title')) ||
                    filled(data_get($this->data, 'video.video_file'))
                ) {

                    Video::create([

                        'content_item_id' => $record->id,

                        'title' => data_get(
                            $this->data,
                            'video.title'
                        ),

                        'video_file' => data_get(
                            $this->data,
                            'video.video_file'
                        ),

                    ]);
                }

                break;

            /*
            |--------------------------------------------------------------------------
            | گام به گام
            |--------------------------------------------------------------------------
            */

            case 'step_by_step':

                $step = StepByStep::create([

                    'content_item_id' => $record->id,

                ]);

                foreach (
                    data_get(
                        $this->data,
                        'stepByStep',
                        []
                    ) as $page
                ) {

                    StepByStepPage::create([

                        'step_by_step_id' => $step->id,

                        'title' => $page['title'] ?? null,

                        'page_number' => $page['sort_order'] ?? 1,

                        'image' => $page['image'],

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

                PdfFile::create([

                    'content_item_id' => $record->id,

                    'title' => data_get(
                        $this->data,
                        'pdfFile.title'
                    ),

                    'file' => data_get(
                        $this->data,
                        'pdfFile.file'
                    ),

                ]);

                break;
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'محتوای آموزشی با موفقیت ایجاد شد.';
    }
}
