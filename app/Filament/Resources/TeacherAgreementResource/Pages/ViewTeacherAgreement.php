<?php

namespace App\Filament\Resources\TeacherAgreementResource\Pages;

use App\Filament\Resources\TeacherAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacherAgreement extends ViewRecord
{
    protected static string $resource = TeacherAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('print')
                ->label('چاپ')
                ->icon('heroicon-o-printer')
                ->url(fn() => route('agreement.print', $this->record))
                ->openUrlInNewTab(),

        ];
    }
}
