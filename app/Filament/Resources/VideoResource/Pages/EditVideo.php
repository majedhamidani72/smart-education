<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use App\Services\VideoService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EditVideo extends EditRecord
{
    protected static string $resource = VideoResource::class;



    protected function mutateFormDataBeforeFill(
        array $data
    ): array {

        $this->record->load([

            'contentItem.section.chapter.book',

        ]);



        $contentItem = $this->record->contentItem;



        if ($contentItem) {


            $data['title'] =
                $contentItem->title;



            $data['page_number'] =
                $contentItem->page_number;



            if ($contentItem->section) {


                $data['section_id'] =
                    $contentItem->section_id;



                $data['chapter_id'] =
                    $contentItem
                    ->section
                    ->chapter_id;



                $data['book_id'] =
                    $contentItem
                    ->section
                    ->chapter
                    ->book_id;
            }
        }




        $data['download_allowed'] =
            $this->record->download_allowed;



        $data['processing_status'] =
            $this->record->processing_status;



        $data['rejected_reason'] =
            $this->record->rejected_reason;





        /*
        |--------------------------------------------------------------------------
        | مقداردهی فایل قبلی برای FileUpload
        |--------------------------------------------------------------------------
        */


        if (

            filled($this->record->directory)

            &&

            filled($this->record->filename)

        ) {


            $data['video_file'] = [

                $this->record->directory
                    .
                    '/'
                    .
                    $this->record->filename,

            ];
        }



        return $data;
    }
    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {


        $videoFile =
            $data['video_file'] ?? null;



        unset(
            $data['video_file']
        );





        /*
        |--------------------------------------------------------------------------
        | Update ContentItem
        |--------------------------------------------------------------------------
        */


        if ($record->contentItem) {


            $record->contentItem->update([


                'section_id' =>
                $data['section_id'],


                'title' =>
                $data['title'],


                'slug' =>
                Str::slug(
                    $data['title']
                ),


                'page_number' =>
                $data['page_number'] ?? null,


            ]);
        }





        /*
        |--------------------------------------------------------------------------
        | Remove Form Only Fields
        |--------------------------------------------------------------------------
        */


        unset(

            $data['book_id'],

            $data['chapter_id'],

            $data['section_id'],

            $data['title'],

            $data['page_number']

        );






        /*
        |--------------------------------------------------------------------------
        | Approval Workflow
        |--------------------------------------------------------------------------
        */


        if (

            isset($data['processing_status'])

            &&

            $data['processing_status'] === 'approved'

        ) {


            $data['approved_by'] =
                Auth::id();



            $data['approved_at'] =
                now();



            $data['rejected_reason'] =
                null;
        }





        if (

            isset($data['processing_status'])

            &&

            $data['processing_status'] === 'rejected'

        ) {


            $data['approved_by'] =
                Auth::id();



            $data['approved_at'] =
                null;
        }






        /*
        |--------------------------------------------------------------------------
        | Detect New File
        |--------------------------------------------------------------------------
        */


        $newVideoFile = null;



        if (

            is_array($videoFile)

            &&

            isset($videoFile[0])

        ) {


            $oldFile =

                $record->directory
                .
                '/'
                .
                $record->filename;



            if (

                $videoFile[0] !== $oldFile

            ) {


                $newVideoFile =
                    $videoFile[0];
            }
        }






        return app(VideoService::class)->update(

            $record,

            $data,

            $newVideoFile

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






    protected function getSavedNotificationTitle(): ?string
    {
        return 'ویدئو با موفقیت ویرایش شد.';
    }
}
