<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\ContentItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    /**
     * نوار مسیر بالای صفحه — از روی مسیر واقعی خودِ سوال (نه
     * پارامترهای آدرس) محاسبه می‌شود تا حتی اگر مستقیم وارد این
     * صفحه شده باشی، درست باشد.
     */
    public function getSubheading(): ?string
    {
        $book = \App\Models\Book::with('appGradeSubject.grade', 'appGradeSubject.app')
            ->find($this->record->book_id);

        if (! $book) {
            return null;
        }

        $chapter = $this->record->chapter?->title;

        $section = $this->record->section?->title;

        $path = collect([
            $book->appGradeSubject?->app?->title,
            'پایه '.$book->appGradeSubject?->grade?->title,
            $book->title,
            $chapter,
            $section,
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
                ->url(static::getResource()::getUrl('index', array_filter([
                    'book_id' => request()->query('book_id', $this->record->book_id),
                    'chapter_id' => request()->query('chapter_id', $this->record->chapter_id),
                    'section_id' => request()->query('section_id', $this->record->section_id),
                ]))),

            Actions\DeleteAction::make(),

            Actions\RestoreAction::make(),

            Actions\ForceDeleteAction::make(),

        ];
    }

    /**
     * بازسازی زنجیره‌ی اپلیکیشن/پایه/درس/کتاب/فصل/بخش — اول از
     * ستون‌های مستقیم خودِ سوال (book_id/chapter_id/section_id،
     * که الان مسیر اصلی‌اند)، و فقط اگر آن‌ها خالی بودند، از روی
     * content_item_id قدیمی.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['book_id'])) {

            $book = \App\Models\Book::query()
                ->with('appGradeSubject')
                ->find($data['book_id']);

            if ($book) {

                $data['book_id'] = $book->id;

                $data['chapter_id'] = $data['chapter_id'] ?? null;

                $data['section_id'] = $data['section_id'] ?? null;

                $data['subject_id'] = $book->appGradeSubject?->subject_id;

                $data['grade_id'] = $book->appGradeSubject?->grade_id;

                $data['app_id'] = $book->appGradeSubject?->app_id;

                return $data;
            }
        }

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

    /**
     * وقتی سوال «در انتظار بررسی» است، معلم فقط می‌تواند ببیندش
     * (فرم غیرفعال/فقط‌خواندنی) — تا ادمین/سوپرادمین تصمیم بگیرد
     * (تأیید یا رد). به‌محض رد شدن، دوباره قابل ویرایش می‌شود.
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
     * اعتبارسنجی «حداقل یک گزینه‌ی صحیح» الان مستقیم روی خودِ
     * Repeater (توی QuestionResource::form) تعریف شده — چون آن
     * روش، دقیقاً روی همان داده‌ی واقعی‌ای که Filament موقع
     * اعتبارسنجی/ذخیره استفاده می‌کند کار می‌کند، نه یک کپی جدا
     * از وضعیت فرم که ممکن بود گاهی هماهنگ نباشد (همان چیزی که
     * قبلاً باعث خطای الکی می‌شد).
     */

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'سوال با موفقیت ویرایش شد.';
    }
}
