<?php

namespace App\Filament\Resources\QuestionTopicResource\Pages;

use App\Filament\Resources\QuestionTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestionTopics extends ListRecords
{
    protected static string $resource = QuestionTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
