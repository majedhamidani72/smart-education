<?php
namespace App\Filament\Resources\PowerpointResource\Pages;
use App\Filament\Resources\PowerpointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPowerpoint extends EditRecord {
    protected static string $resource = PowerpointResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->label('حذف')];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
