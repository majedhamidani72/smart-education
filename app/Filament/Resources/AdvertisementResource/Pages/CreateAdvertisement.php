<?php

namespace App\Filament\Resources\AdvertisementResource\Pages;

use App\Filament\Resources\AdvertisementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvertisement extends CreateRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تبلیغ با موفقیت ایجاد شد.';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
