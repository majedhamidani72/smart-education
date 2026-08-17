<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

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
        if (isset($data['password']) && filled($data['password'])) {

            $data['password'] = Hash::make($data['password']);

        } else {

            // اگر رمز خالی فرستاده شده، یعنی ادمین نمی‌خواهد
            // رمز را عوض کند؛ رمز قبلی دست‌نخورده می‌ماند.
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // اطمینان از این‌که نقش Admin هیچ‌وقت از این کاربر
        // برداشته نمی‌شود، حتی اگر جای دیگری تغییر کرده باشد.
        $this->record->syncRoles(['Admin']);
    }

    protected function getRedirectUrl(): string
    {
        return AdminResource::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'اطلاعات ادمین با موفقیت ویرایش شد.';
    }
}
