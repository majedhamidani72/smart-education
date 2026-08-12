<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;


    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

            Actions\RestoreAction::make(),

            Actions\ForceDeleteAction::make(),

        ];
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            isset($data['status'])
            &&
            $data['status'] === 'active'
        ) {

            $data['reviewed_by'] = auth()->id();

        }


        return $data;
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function getSavedNotificationTitle(): ?string
    {
        return 'آزمون با موفقیت ویرایش شد.';
    }
}
