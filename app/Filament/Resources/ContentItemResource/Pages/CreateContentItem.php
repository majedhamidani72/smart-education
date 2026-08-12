<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContentItem extends CreateRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = ContentItemResource::class;

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
        return 'محتوا با موفقیت ایجاد شد.';
    }

    /**
     * قبل از ذخیره
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
