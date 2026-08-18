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
     * content_item_id واقعی رکورد، دقیقاً مثل EditContentItem.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['content_item_id'])) {
            return $data;
        }

        $contentItem = ContentItem::query()
            ->with('section.chapter.book.appGradeSubject')
            ->find($data['content_item_id']);

        $section = $contentItem?->section;

        if ($section && $section->chapter && $section->chapter->book) {

            $book = $section->chapter->book;

            $data['section_id'] = $section->id;

            $data['chapter_id'] = $section->chapter_id;

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

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'سوال با موفقیت ویرایش شد.';
    }
}
