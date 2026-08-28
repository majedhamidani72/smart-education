<?php
namespace App\Filament\Resources\PowerpointResource\Pages;
use App\Filament\Resources\PowerpointResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListPowerpoints extends ListRecords { protected static string $resource = PowerpointResource::class; protected function getHeaderActions(): array { return [Actions\CreateAction::make()->label('افزودن پاورپوینت')]; } }
