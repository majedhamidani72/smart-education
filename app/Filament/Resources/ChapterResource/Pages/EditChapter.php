<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChapter extends EditRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = ChapterResource::class;

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
        return 'فصل با موفقیت ویرایش شد.';
    }
}
