<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use App\Models\Book;
use App\Models\Grade;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    /**
     * بازسازی فیلدهای کمکی فرم از روی planable واقعی رکورد.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['planable_type'] ?? null) === Grade::class) {

            $data['access_type'] = 'grade';

            $data['grade_only_id'] = $data['planable_id'];

        } elseif (($data['planable_type'] ?? null) === Book::class) {

            $data['access_type'] = 'book';

            $book = Book::with('appGradeSubject')->find($data['planable_id']);

            if ($book && $book->appGradeSubject) {

                $data['book_id'] = $book->id;

                $data['subject_id'] = $book->appGradeSubject->subject_id;

                $data['grade_id'] = $book->appGradeSubject->grade_id;

                $data['app_id'] = $book->appGradeSubject->app_id;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['access_type'] === 'grade') {

            $data['planable_type'] = Grade::class;

            $data['planable_id'] = $data['grade_only_id'];

        } else {

            $data['planable_type'] = Book::class;

            $data['planable_id'] = $data['book_id'];
        }

        unset(
            $data['access_type'],
            $data['grade_only_id'],
            $data['app_id'],
            $data['grade_id'],
            $data['subject_id'],
            $data['book_id'],
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'پلن با موفقیت ویرایش شد.';
    }
}
