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
use Illuminate\Support\Str;

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

        // عنوان نهایی محتوا از روی همان فیلد اختصاصی نوع محتوا
        // بازسازی می‌شود (همان منطق CreateContentItem).
        $title = $this->resolveTitle($data);

        if (filled($title)) {

            $data['title'] = $title;

            $data['slug'] = Str::slug($title);
        }

        return $data;
    }

    protected function resolveTitle(array $data): ?string
    {
        $slug = ContentType::query()
            ->whereKey($data['content_type_id'] ?? null)
            ->value('slug');

        return match ($slug) {

            'teaching' => data_get($data, 'video.title'),

            'step_by_step' => collect(data_get($data, 'stepByStep', []))
                ->first()['title'] ?? null,

            'sample_questions' => data_get($data, 'pdfFile.title'),

            default => null,
        };
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
            | تدریس
            |--------------------------------------------------------------------------
            */

            case 'teaching':

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
            | گام به گام
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

                        'title' => $page['title'] ?? null,

                        'page_number' => $page['sort_order'] ?? 1,

                        'image' => $page['image'],

                        'sort_order' => $page['sort_order'] ?? 1,

                        'is_free' => false,


                    ]);
                }

                break;

            /*
            |--------------------------------------------------------------------------
            | نمونه سوال
            |--------------------------------------------------------------------------
            */

            case 'sample_questions':

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
