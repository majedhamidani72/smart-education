<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use App\Models\Book;
use App\Models\Grade;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'پلن با موفقیت ایجاد شد.';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
