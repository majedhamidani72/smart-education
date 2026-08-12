<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = GradeResource::class;

    /**
     * پس از ثبت، بازگشت به لیست پایه‌ها
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
        return 'پایه با موفقیت ایجاد شد.';
    }
}
