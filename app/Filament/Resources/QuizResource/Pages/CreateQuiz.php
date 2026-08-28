<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Book;
use App\Services\QuizTemplateService;
use App\Models\Quiz;
use Illuminate\Validation\ValidationException;


class CreateQuiz extends CreateRecord
{

    protected static string $resource = QuizResource::class;



    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $user = Auth::user();



        if ($user) {

            $data['created_by'] = $user->id;
        }

        $bookId = $data['book_id'] ?? null;
        unset($data['book_id']);
        $scope = $data['quizable_type'] ?? null;
        $data['is_template'] = true;
        $data['template_book_id'] = $bookId;
        $data['template_scope'] = $scope;
        $data['quizable_type'] = Book::class;
        $data['quizable_id'] = $bookId;
        $book = Book::find($bookId);
        if (Quiz::query()->where('is_template', true)
            ->where('template_book_id', $bookId)
            ->where('template_scope', $scope)->exists()) {
            throw ValidationException::withMessages([
                'quizable_type' => 'برای این کتاب و این سطح، قبلاً تنظیم مشترک ساخته شده است؛ همان تنظیم را ویرایش کنید.',
            ]);
        }
        $data['title'] = $book
            ? 'تنظیم '.$this->scopeLabel($scope ?? '').' '.$book->title
            : ($data['title'] ?? 'تنظیم آزمون');

        // معلم نمی‌تواند وضعیت را خودش تعیین کند — همیشه با «در
        // انتظار بررسی» شروع می‌شود.
        $isReviewer = $user?->hasRole('SuperAdmin') || $user?->hasRole('Admin');

        if (! $isReviewer) {

            $data['status'] = 'pending';

            $data['rejection_reason'] = null;
        }



        return $data;
    }

    protected function afterCreate(): void
    {
        app(QuizTemplateService::class)->sync($this->record);
    }

    private function scopeLabel(string $type): string
    {
        return match ($type) {
            \App\Models\Section::class => 'آزمون‌های درس/بخش',
            \App\Models\Chapter::class => 'آزمون‌های فصل',
            default => 'آزمون جامع کتاب',
        };
    }




    protected function getRedirectUrl(): string
    {
        return filled($this->previousUrl)
            ? $this->previousUrl
            : $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {

        return 'آزمون با موفقیت ایجاد شد.';
    }
}
