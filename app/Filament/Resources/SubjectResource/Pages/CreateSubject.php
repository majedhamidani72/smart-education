<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubject extends CreateRecord
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = SubjectResource::class;

    /**
     * بازگشت به لیست پس از ثبت
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
        return 'درس با موفقیت ایجاد شد.';
    }
}
