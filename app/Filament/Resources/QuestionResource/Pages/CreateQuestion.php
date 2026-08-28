<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionResource\Pages\Concerns\HandlesMissingQuestionUploads;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    use HandlesMissingQuestionUploads;

    protected static string $resource = QuestionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        $isReviewer = auth()->user()?->hasRole('Admin')
            || auth()->user()?->hasRole('SuperAdmin');

        $allowedReviewerStatuses = ['pending', 'approved', 'rejected'];

        $data['status'] = $isReviewer && in_array($data['status'] ?? null, $allowedReviewerStatuses, true)
            ? $data['status']
            : 'pending';

        if ($isReviewer && $data['status'] === 'approved') {
            $data['reviewed_by'] = auth()->id();
            $data['rejection_reason'] = null;
        } elseif (! $isReviewer) {
            unset($data['reviewed_by'], $data['rejection_reason']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'سوال با موفقیت ایجاد شد.';
    }
}
