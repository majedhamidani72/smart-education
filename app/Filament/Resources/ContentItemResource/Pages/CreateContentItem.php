<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use App\Models\ContentType;
use App\Models\PdfFile;
use App\Models\StepByStep;
use App\Models\StepByStepPage;
use App\Models\Video;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditContentItem extends EditRecord
{
    protected static string $resource = ContentItemResource::class;

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

            // وضعیت به همان مقداری که از قبل روی رکورد بوده
            // برمی‌گردد؛ یعنی معلم عملاً نمی‌تواند وضعیت را از این
            // مسیر تغییر دهد.
            $data['status'] = $this->record->status;

            unset($data['reviewed_by'], $data['reviewed_at'], $data['rejection_reason']);

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

                $step = StepByStep::firstOrCreate(

                    [
                        'content_item_id' => $record->id,
                    ]

                );

                $step->pages()->delete();

                foreach (

                    data_get(
                        $this->data,
                        'stepByStep',
                        []
                    )

                    as $page

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
    protected function extractFileMeta(?string $path): array
    {
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

    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

            Actions\ForceDeleteAction::make(),

            Actions\RestoreAction::make(),

        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'محتوای آموزشی با موفقیت ویرایش شد.';
    }
}
