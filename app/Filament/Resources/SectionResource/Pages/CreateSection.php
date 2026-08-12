<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSection extends CreateRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = SectionResource::class;

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
        return 'بخش با موفقیت ایجاد شد.';
    }
}
