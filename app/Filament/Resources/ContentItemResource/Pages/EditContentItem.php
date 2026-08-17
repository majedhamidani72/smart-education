<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use App\Models\ContentType;
use App\Models\PdfFile;
use App\Models\StepByStep;
use App\Models\StepByStepPage;
use App\Models\Video;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContentItem extends EditRecord
{
    protected static string $resource = ContentItemResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            isset($data['status']) &&
            in_array(
                $data['status'],
                [
                    'approved',
                    'published',
                ],
                true
            )
        ) {
            $data['reviewed_by'] = auth()->id();

            $data['reviewed_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        $type = ContentType::find(
            $record->content_type_id
        );

        if (! $type) {
            return;
        }

        switch ($type->slug) {

            /*
            |--------------------------------------------------------------------------
            | Video
            |--------------------------------------------------------------------------
            */

            case 'video':

                Video::updateOrCreate(

                    [
                        'content_item_id' => $record->id,
                    ],

                    [
                        'title' => data_get(
                            $this->data,
                            'video.title'
                        ),

                        'video_file' => data_get(
                            $this->data,
                            'video.video_file'
                        ),

                    ]

                );

                break;

            /*
            |--------------------------------------------------------------------------
            | Step By Step
            |--------------------------------------------------------------------------
            */

            case 'step_by_step':

                $step = StepByStep::firstOrCreate(

                    [
                        'content_item_id' => $record->id,
                    ]

                );

                $step->pages()->delete();

                foreach (

                    data_get(
                        $this->data,
                        'stepByStep',
                        []
                    )

                    as $page

                ) {

                    StepByStepPage::create([

                        'step_by_step_id' => $step->id,

                        'page_number' => $page['sort_order'] ?? 1,

                        'image' => $page['image'],

                        'sort_order' => $page['sort_order'] ?? 1,

                        'is_free' => false,


                    ]);
                }

                break;

            /*
            |--------------------------------------------------------------------------
            | Sample Question
            |--------------------------------------------------------------------------
            */

            case 'sample_question':

                PdfFile::updateOrCreate(

                    [
                        'content_item_id' => $record->id,
                    ],

                    [
                        'title' => data_get(
                            $this->data,
                            'pdfFile.title'
                        ),

                        'file' => data_get(
                            $this->data,
                            'pdfFile.file'
                        ),

                    ]

                );

                break;
        }
    }

    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

            Actions\ForceDeleteAction::make(),

            Actions\RestoreAction::make(),

        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'محتوای آموزشی با موفقیت ویرایش شد.';
    }
}
