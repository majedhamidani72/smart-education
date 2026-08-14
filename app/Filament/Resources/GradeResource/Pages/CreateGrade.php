<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use App\Models\Subject;
use App\Models\GradeSubject;
use Filament\Resources\Pages\CreateRecord;

class CreateGrade extends CreateRecord
{
    protected static string $resource = GradeResource::class;


    protected function afterCreate(): void
    {
        $subjects = Subject::where('is_active', true)
            ->get();


        foreach ($subjects as $subject) {

            GradeSubject::create([

                'grade_id' => $this->record->id,

                'subject_id' => $subject->id,

                'is_active' => true,

                'sort_order' => 1,

            ]);

        }
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function getCreatedNotificationTitle(): ?string
    {
        return 'پایه با موفقیت ایجاد شد.';
    }
}
