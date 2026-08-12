<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentItems extends ListRecords
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = ContentItemResource::class;

    /**
     * دکمه‌های بالا
     */
    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make(),

        ];
    }
}
