<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
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


    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            isset($data['status'])
            &&
            in_array(
                $data['status'],
                [
                    'approved',
                ],
                true
            )
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
        return 'سوال با موفقیت ویرایش شد.';
    }
}
