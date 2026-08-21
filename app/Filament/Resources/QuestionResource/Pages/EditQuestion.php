<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\ContentItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

            Actions\RestoreAction::make(),

            Actions\ForceDeleteAction::make(),

        ];
    }

    /**
     * بازسازی زنجیره‌ی اپلیکیشن/پایه/درس/کتاب/فصل/بخش از روی
     * content_item_id واقعی رکورد. مثل EditContentItem، از رابطه‌ی
     * مستقیم chapter استفاده می‌شود (نه فقط section.chapter) —
     * چون محتوا می‌تواند بدون بخش، مستقیم به فصل وصل باشد.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['content_item_id'])) {
            return $data;
        }

        $contentItem = ContentItem::query()
            ->with('chapter.book.appGradeSubject', 'section')
            ->find($data['content_item_id']);

        $chapter = $contentItem?->chapter;

        if ($chapter && $chapter->book) {

            $book = $chapter->book;

            $data['section_id'] = $contentItem->section_id;

            $data['chapter_id'] = $chapter->id;

            $data['book_id'] = $book->id;

            $data['subject_id'] = $book->appGradeSubject?->subject_id;

            $data['grade_id'] = $book->appGradeSubject?->grade_id;

            $data['app_id'] = $book->appGradeSubject?->app_id;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $isReviewer = auth()->user()?->hasRole('Admin')
            || auth()->user()?->hasRole('SuperAdmin');

        if (! $isReviewer) {

            // معلم نمی‌تواند وضعیت سوال را از این مسیر تغییر دهد؛
            // ویرایش او خودکار دوباره به «در انتظار بررسی» می‌رود.
            $data['status'] = 'pending';

            $data['rejection_reason'] = null;

            $data['reviewed_by'] = null;

        } elseif (($data['status'] ?? null) === 'approved') {

            $data['reviewed_by'] = auth()->id();
        }

        return $data;
    }

    /**
     * بدون حداقل یک گزینه‌ی «پاسخ صحیح»، سوال اصلاً ذخیره نمی‌شود.
     * چون گزینه‌ها یک Repeater رابطه‌ای هستند، داخل $data خودِ
     * mutateFormDataBeforeSave نیستند — باید مستقیم از وضعیت کامل
     * فرم خوانده شوند.
     */
    protected function beforeSave(): void
    {
        $options = $this->form->getState()['options'] ?? [];

        $hasCorrect = collect($options)->contains(
            fn($option) => ($option['is_correct'] ?? false) === true
        );

        if (! $hasCorrect) {

            \Filament\Notifications\Notification::make()
                ->title('حداقل یکی از گزینه‌ها باید «پاسخ صحیح» باشد.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'سوال با موفقیت ویرایش شد.';
    }
}
