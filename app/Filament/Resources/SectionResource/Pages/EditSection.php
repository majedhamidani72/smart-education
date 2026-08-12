<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSection extends EditRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = SectionResource::class;

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
     * بازگشت به لیست
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
        return 'بخش با موفقیت ویرایش شد.';
    }
}
