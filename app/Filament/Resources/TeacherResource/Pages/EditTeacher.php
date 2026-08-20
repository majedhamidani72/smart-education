<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

/**
 * ویرایش معلم
 * --------------------------------------------------------------------
 * فقط اطلاعات حساب (اسم، موبایل، رمز، فعال/غیرفعال). مدیریت
 * کتاب‌های تدریسی از تب «کتاب‌های تدریسی» پایین همین صفحه انجام
 * می‌شود (BooksRelationManager) — نه از این فرم.
 */
class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

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

            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // نقش Teacher هیچ‌وقت از این کاربر برداشته نمی‌شود.
        $this->record->syncRoles(['Teacher']);
    }

    protected function getRedirectUrl(): string
    {
        return TeacherResource::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'اطلاعات معلم با موفقیت ویرایش شد.';
    }
}
