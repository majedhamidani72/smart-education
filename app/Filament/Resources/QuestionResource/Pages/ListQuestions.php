<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // به‌جای فرم استاندارد «ایجاد» (که مسیر آموزشی را هر
            // بار از نو می‌خواست)، مستقیم به صفحه‌ی «افزودن سریع
            // سوال» می‌رود — که همان کار را بهتر انجام می‌دهد.
            Actions\Action::make('create')
                ->label('ایجاد سوال')
                ->icon('heroicon-o-plus')
                ->url(\App\Filament\Pages\AddQuestionsToBank::getUrl()),

        ];
    }
}
