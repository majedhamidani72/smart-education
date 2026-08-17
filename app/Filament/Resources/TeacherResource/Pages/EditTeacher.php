<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\TeacherAssignment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    // کتابی که در فرم انتخاب شده و باید بعد از ذخیره به‌عنوان
    // دسترسی معلم به‌روزرسانی شود.
    protected ?int $bookIdToAssign = null;

    protected function getHeaderActions(): array
    {
        return [

            Actions\DeleteAction::make(),

            Actions\RestoreAction::make(),

            Actions\ForceDeleteAction::make(),

        ];
    }

    /**
     * وقتی فرم ویرایش باز می‌شود، آخرین دسترسیِ فعالِ این معلم
     * (در صورت وجود) در فیلدهای اپلیکیشن/پایه/درس/کتاب بارگذاری
     * می‌شود تا سوپرادمین ببیند الان دقیقاً معلم به چه چیزی
     * دسترسی دارد.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $assignment = TeacherAssignment::query()
            ->where('teacher_id', $this->record->id)
            ->where('is_active', true)
            ->with('book.appGradeSubject')
            ->latest()
            ->first();

        if ($assignment && $assignment->book && $assignment->book->appGradeSubject) {

            $data['book_id'] = $assignment->book_id;

            $data['subject_id'] = $assignment->book->appGradeSubject->subject_id;

            $data['grade_id'] = $assignment->book->appGradeSubject->grade_id;

            $data['app_id'] = $assignment->book->appGradeSubject->app_id;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->bookIdToAssign = $data['book_id'] ?? null;

        unset(
            $data['app_id'],
            $data['grade_id'],
            $data['subject_id'],
            $data['book_id'],
        );

        if (isset($data['password']) && filled($data['password'])) {

            $data['password'] = Hash::make($data['password']);

        } else {

            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // نقش Teacher هیچ‌وقت از این کاربر برداشته نمی‌شود.
        $this->record->syncRoles(['Teacher']);

        if (! $this->bookIdToAssign) {
            return;
        }

        TeacherAssignment::withTrashed()->updateOrCreate(
            [
                'teacher_id' => $this->record->id,
                'book_id' => $this->bookIdToAssign,
            ],
            [
                'assigned_by' => auth()->id(),
                'is_active' => true,
                'deleted_at' => null,
            ]
        );
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'اطلاعات معلم با موفقیت ویرایش شد.';
    }
}
