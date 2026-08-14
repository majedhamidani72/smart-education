<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;


    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

            Actions\RestoreAction::make(),

            Actions\ForceDeleteAction::make(),

        ];
    }


    protected function mutateFormDataBeforeSave(
        array $data
    ): array {

        if (
            isset($data['password'])
            &&
            filled($data['password'])
        ) {

            $data['password'] = Hash::make(
                $data['password']
            );

        } else {

            unset($data['password']);

        }


        unset($data['roles']);


        return $data;
    }


    protected function afterSave(): void
    {
        if (
            isset($this->data['roles'])
            &&
            filled($this->data['roles'])
        ) {

            $this->record->syncRoles(
                $this->data['roles']
            );

        }
    }


    protected function getSavedNotificationTitle(): ?string
    {
        return 'اطلاعات کاربر با موفقیت ویرایش شد.';
    }
}
