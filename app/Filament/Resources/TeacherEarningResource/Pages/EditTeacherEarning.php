<?php

namespace App\Filament\Resources\TeacherEarningResource\Pages;

use App\Filament\Resources\TeacherEarningResource;
use Filament\Resources\Pages\EditRecord;

class EditTeacherEarning extends EditRecord
{
    protected static string $resource = TeacherEarningResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'به‌روزرسانی شد.';
    }
}
