<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Section;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use App\Services\QuizTemplateService;
use App\Models\Quiz;
use Illuminate\Validation\ValidationException;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

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

    /**
     * بازسازی مسیر آموزشی (اپلیکیشن/پایه/درس/کتاب) از روی
     * quizable واقعی رکورد — چون این فیلدها فقط برای فیلتر کردن
     * هستند و مستقیم ذخیره نمی‌شوند.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $book = $this->record->is_template
            ? Book::with('appGradeSubject')->find($this->record->template_book_id)
            : match ($data['quizable_type'] ?? null) {

            Book::class => Book::with('appGradeSubject')->find($data['quizable_id']),

            Chapter::class => Chapter::with('book.appGradeSubject')
                ->find($data['quizable_id'])?->book,

            Section::class => Section::with('chapter.book.appGradeSubject')
                ->find($data['quizable_id'])?->chapter?->book,

            default => null,
        };

        if ($book && $book->appGradeSubject) {

            $data['book_id'] = $book->id;

            $data['subject_id'] = $book->appGradeSubject->subject_id;

            $data['grade_id'] = $book->appGradeSubject->grade_id;

            $data['app_id'] = $book->appGradeSubject->app_id;
        }

        if (($data['quizable_type'] ?? null) === Section::class) {

            $data['section_chapter_filter'] = Section::find($data['quizable_id'])?->chapter_id;
        }

        if ($this->record->is_template) {
            $data['quizable_type'] = $this->record->template_scope;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(QuizTemplateService::class)->sync($this->record);
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->url(fn (): string => $this->previousPageUrl());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $bookId = $data['book_id'] ?? $this->record->template_book_id;
        unset($data['book_id']);
        $data['template_book_id'] = $bookId;
        $scope = $data['quizable_type'] ?? $this->record->template_scope;
        $data['template_scope'] = $scope;
        $data['quizable_type'] = Book::class;
        $data['quizable_id'] = $bookId;
        $data['is_template'] = true;
        if (Quiz::query()->where('is_template', true)
            ->where('template_book_id', $bookId)
            ->where('template_scope', $scope)
            ->whereKeyNot($this->record->id)->exists()) {
            throw ValidationException::withMessages([
                'quizable_type' => 'برای این کتاب و این سطح، قبلاً تنظیم مشترک دیگری وجود دارد.',
            ]);
        }
        $isReviewer = auth()->user()?->hasRole('SuperAdmin')
            || auth()->user()?->hasRole('Admin');

        if ($isReviewer) {

            /*
            |--------------------------------------------------------------------------
            | ثبت مدیر تایید کننده
            |--------------------------------------------------------------------------
            */

            if (in_array($data['status'] ?? null, ['active', 'rejected'], true)) {

                $data['reviewed_by'] = auth()->id();
            }

        } else {

            // معلم نمی‌تواند وضعیت را مستقیم تغییر بدهد. اگر آزمون
            // قبلاً رد شده بود و معلم دارد اصلاحش می‌کند، وضعیت
            // خودکار به «در انتظار بررسی» برمی‌گردد تا ادمین/
            // سوپرادمین دوباره بررسی کنند؛ دلیل رد قبلی هم پاک
            // می‌شود چون دیگر معتبر نیست.
            if ($this->record->status === 'rejected') {

                $data['status'] = 'pending';

                $data['rejection_reason'] = null;

                $data['reviewed_by'] = null;

            } else {

                unset($data['status']);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousPageUrl();
    }

    protected function previousPageUrl(): string
    {
        return filled($this->previousUrl)
            ? $this->previousUrl
            : $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'آزمون با موفقیت ویرایش شد.';
    }
}
