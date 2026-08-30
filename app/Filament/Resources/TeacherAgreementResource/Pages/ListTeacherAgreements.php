<?php

namespace App\Filament\Resources\TeacherAgreementResource\Pages;

use App\Filament\Resources\TeacherAgreementResource;
use Filament\Resources\Pages\ListRecords;

class ListTeacherAgreements extends ListRecords
{
    protected static string $resource = TeacherAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
