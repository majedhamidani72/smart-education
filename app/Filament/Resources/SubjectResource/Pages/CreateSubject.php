<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use App\Models\Grade;
use App\Models\GradeSubject;
use Filament\Resources\Pages\CreateRecord;

class CreateSubject extends CreateRecord
{
    protected static string $resource = SubjectResource::class;


    protected function afterCreate(): void
    {
        $grades = Grade::where('is_active', true)
            ->get();


        foreach ($grades as $grade) {

            GradeSubject::firstOrCreate([

                'grade_id' => $grade->id,

                'subject_id' => $this->record->id,

            ], [

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
        return 'درس با موفقیت ایجاد شد.';
    }
}
