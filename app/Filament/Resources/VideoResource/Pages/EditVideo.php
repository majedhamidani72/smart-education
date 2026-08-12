<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use App\Services\VideoService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditVideo extends EditRecord
{
    protected static string $resource = VideoResource::class;


    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {


        $videoFile = $data['video_file'] ?? null;


        unset($data['video_file']);



        return app(VideoService::class)->update(

            $record,

            $data,

            is_string($videoFile) && filled($videoFile)
                ? $videoFile
                : null

        );
    }



    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

        ];
    }



    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl(
            'index'
        );
    }
}
