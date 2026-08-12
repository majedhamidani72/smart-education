<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use App\Services\VideoService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;


class CreateVideo extends CreateRecord
{

    protected static string $resource = VideoResource::class;




    protected function handleRecordCreation(
        array $data
    ): Model {



        $videoFile = $data['video_file'] ?? null;



        unset($data['video_file']);


        if (! is_string($videoFile) || blank($videoFile)) {


            throw new \Exception(
                'Video file is required.'
            );

        }


        return app(VideoService::class)->create(

            $data,

            $videoFile

        );

    }


    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl(
            'index'
        );
    }

}
