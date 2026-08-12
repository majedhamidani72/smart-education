<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubject extends EditRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = SubjectResource::class;

    /**
     * دکمه‌های بالای صفحه
     */
    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

            Actions\ForceDeleteAction::make(),

            Actions\RestoreAction::make(),

        ];
    }

    /**
     * بازگشت به لیست پس از ویرایش
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * پیام موفقیت
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'درس با موفقیت ویرایش شد.';
    }
}
