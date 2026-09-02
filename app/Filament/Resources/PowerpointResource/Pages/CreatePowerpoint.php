<?php
namespace App\Filament\Resources\PowerpointResource\Pages;
use App\Filament\Resources\PowerpointResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePowerpoint extends CreateRecord {
    protected static string $resource = PowerpointResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
