<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use App\Models\GradeSubject;
use App\Models\Subject;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGrade extends EditRecord
{
    protected static string $resource = GradeResource::class;


    protected function afterSave(): void
    {
        $subjects = Subject::where('is_active', true)
            ->get();


        foreach ($subjects as $subject) {

            GradeSubject::firstOrCreate([

                'grade_id' => $this->record->id,

                'subject_id' => $subject->id,

            ], [

                'is_active' => true,

                'sort_order' => 1,

            ]);

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
        return $this->getResource()::getUrl('index');
    }


    protected function getSavedNotificationTitle(): ?string
    {
        return 'پایه با موفقیت ویرایش شد.';
    }
}
