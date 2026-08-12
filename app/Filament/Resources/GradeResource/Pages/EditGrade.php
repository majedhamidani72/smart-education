<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGrade extends EditRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = GradeResource::class;

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
     * پس از ویرایش، بازگشت به لیست
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
        return 'پایه با موفقیت ویرایش شد.';
    }
}
