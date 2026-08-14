<?php

namespace App\Filament\Resources\TeacherAssignmentResource\Pages;


use App\Filament\Resources\TeacherAssignmentResource;


use Filament\Actions;

use Filament\Resources\Pages\EditRecord;



class EditTeacherAssignment extends EditRecord
{


    protected static string $resource =
        TeacherAssignmentResource::class;




    protected function getHeaderActions(): array
    {

        return [

            Actions\DeleteAction::make(),

            Actions\RestoreAction::make(),

            Actions\ForceDeleteAction::make(),

        ];

    }





    /**
     * ثبت کننده اصلی تغییر نمی‌کند
     */
    protected function mutateFormDataBeforeSave(
        array $data
    ): array
    {

        unset(
            $data['assigned_by']
        );


        return $data;

    }





    protected function getSavedNotificationTitle(): ?string
    {

        return 'اختصاص معلم با موفقیت ویرایش شد.';

    }


}
