<?php

namespace App\Filament\Resources\QuestionTopicResource\Pages;

use App\Filament\Resources\QuestionTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestionTopic extends EditRecord
{
    protected static string $resource = QuestionTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return QuestionTopicResource::getUrl('index');
    }
}
