<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use App\Models\ContentItem;
use App\Services\VideoService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateVideo extends CreateRecord
{
    protected static string $resource = VideoResource::class;



    protected function handleRecordCreation(
        array $data
    ): Model {

        return DB::transaction(function () use ($data) {


            $videoFile = $data['video_file'] ?? null;



            unset(
                $data['video_file']
            );



            if (
                ! is_string($videoFile)
                ||
                blank($videoFile)
            ) {

                throw new \Exception(
                    'Video file is required.'
                );

            }



            $contentItem = ContentItem::create([


                'section_id' => $data['section_id'],


                'content_type_id' => 1,


                'created_by' => auth()->id(),


                'title' => $data['title'],


                'slug' => Str::slug(
                    $data['title']
                ),


                'page_number' =>
                    $data['page_number'] ?? null,


                'status' => 'pending',


                'is_free' => false,


                'sort_order' => 1,


            ]);




            unset(

                $data['book_id'],

                $data['chapter_id'],

                $data['section_id'],

                $data['title'],

                $data['page_number']

            );




            $data['content_item_id'] =
                $contentItem->id;




            return app(VideoService::class)->create(

                $data,

                $videoFile

            );


        });

    }





    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl(
            'index'
        );
    }





    protected function getCreatedNotificationTitle(): ?string
    {
        return 'ویدئو با موفقیت ثبت شد و منتظر بررسی است.';
    }
}
