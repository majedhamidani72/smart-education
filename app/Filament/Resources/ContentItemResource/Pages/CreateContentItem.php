<?php

namespace App\Filament\Resources\ContentItemResource\Pages;

use App\Filament\Resources\ContentItemResource;
use App\Models\ContentType;
use App\Models\PdfFile;
use App\Models\StepByStep;
use App\Models\StepByStepPage;
use App\Models\Video;
use Filament\Resources\Pages\CreateRecord;

class CreateContentItem extends CreateRecord
{
    protected static string $resource = ContentItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
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

                if (
                    filled(data_get($this->data, 'video.title')) ||
                    filled(data_get($this->data, 'video.video_file'))
                ) {

                    Video::create([

                        'content_item_id' => $record->id,

                        'title' => data_get(
                            $this->data,
                            'video.title'
                        ),

                        'video_file' => data_get(
                            $this->data,
                            'video.video_file'
                        ),

                    ]);
                }

                break;

            /*
            |--------------------------------------------------------------------------
            | Step By Step
            |--------------------------------------------------------------------------
            */

            case 'step_by_step':

                $step = StepByStep::create([

                    'content_item_id' => $record->id,

                ]);

                foreach (
                    data_get(
                        $this->data,
                        'stepByStep',
                        []
                    ) as $page
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

                PdfFile::create([

                    'content_item_id' => $record->id,

                    'title' => data_get(
                        $this->data,
                        'pdfFile.title'
                    ),

                    'file' => data_get(
                        $this->data,
                        'pdfFile.file'
                    ),

                ]);

                break;
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'محتوای آموزشی با موفقیت ایجاد شد.';
    }
}
