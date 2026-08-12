<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChapters extends ListRecords
{
    /**
     * Resource مربوطه
     */
    protected static string $resource = ChapterResource::class;

    /**
     * دکمه‌های بالای صفحه
     */
    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make(),

        ];
    }
}
