<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChapter extends CreateRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = ChapterResource::class;

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
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'فصل با موفقیت ایجاد شد.';
    }
}
