<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContentItem extends EditRecord
{
    protected static string $resource = ContentItemResource::class;


    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

            Actions\ForceDeleteAction::make(),

            Actions\RestoreAction::make(),

        ];
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function getSavedNotificationTitle(): ?string
    {
        return 'محتوا با موفقیت ویرایش شد.';
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
                    'published',
                ],
                true
            )
        ) {

            $data['reviewed_by'] = auth()->id();

        }


        return $data;
    }
}
