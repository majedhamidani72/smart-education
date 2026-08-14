<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;


class CreateQuiz extends CreateRecord
{

    protected static string $resource = QuizResource::class;



    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $user = Auth::user();



        if ($user) {

            $data['created_by'] = $user->id;
        }



        return $data;
    }




    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl(
            'index'
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {

        return 'آزمون با موفقیت ایجاد شد.';
    }
}
