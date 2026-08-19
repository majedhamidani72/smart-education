<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\TeacherAssignment;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    // اگر کاربری با همین موبایل قبلاً حذف نرم‌افزاری شده باشد،
    // به‌جای رکورد جدید، همان بازیابی می‌شود.
    protected ?User $deletedUser = null;

    // کتابی که باید بعد از ساخت کاربر، به‌عنوان دسترسی معلم
    // ثبت شود. app_id/grade_id/subject_id فقط فیلترِ رسیدن به
    // همین کتاب بودند، خودشان ذخیره نمی‌شوند.
    protected ?int $bookIdToAssign = null;

    protected int $commissionPercentageToAssign = 30;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->bookIdToAssign = $data['book_id'] ?? null;

        $this->commissionPercentageToAssign = $data['commission_percentage'] ?? 30;

        unset(
            $data['app_id'],
            $data['grade_id'],
            $data['subject_id'],
            $data['book_id'],
        );

        $this->deletedUser = User::withTrashed()
            ->where('mobile', $data['mobile'])
            ->whereNotNull('deleted_at')
            ->first();

        if ($this->deletedUser) {

            $this->deletedUser->restore();

            $this->deletedUser->update([

                'name' => $data['name'],

                'password' => filled($data['password'] ?? null)
                    ? Hash::make($data['password'])
                    : $this->deletedUser->password,

                'must_change_password' => $data['must_change_password'] ?? true,

                'is_active' => $data['is_active'] ?? true,

            ]);

            $this->deletedUser->syncRoles(['Teacher']);

            $this->assignBook($this->deletedUser);

            Notification::make()
                ->title('معلم حذف‌شده بازیابی شد.')
                ->success()
                ->send();

            $this->halt();
        }

        // کاربر فعالِ موجود با همین موبایل (مثلاً دانش‌آموزی که
        // قرار است حالا معلم هم بشود) — به‌جای رکورد جدید، همین
        // حساب به‌روزرسانی می‌شود. رمز فقط اگر واقعاً پر شده باشد
        // تغییر می‌کند، وگرنه رمز قبلی همین کاربر دست‌نخورده
        // می‌ماند.
        $existingActiveUser = User::where('mobile', $data['mobile'])
            ->whereNull('deleted_at')
            ->first();

        if ($existingActiveUser) {

            $existingActiveUser->update([

                'name' => $data['name'],

                'password' => filled($data['password'] ?? null)
                    ? Hash::make($data['password'])
                    : $existingActiveUser->password,

                'must_change_password' => $data['must_change_password'] ?? true,

                'is_active' => $data['is_active'] ?? true,

            ]);

            $existingActiveUser->syncRoles(['Teacher']);

            $this->assignBook($existingActiveUser);

            Notification::make()
                ->title('حساب موجود با همین شماره، به معلم تبدیل شد.')
                ->success()
                ->send();

            $this->halt();
        }

        if (isset($data['password']) && filled($data['password'])) {

            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // نقش «Teacher» همیشه به‌صورت خودکار و ثابت اختصاص داده
        // می‌شود؛ در این فرم هیچ انتخاب نقشی از کاربر گرفته نشده.
        $this->record->assignRole('Teacher');

        $this->assignBook($this->record);
    }

    /**
     * کتاب انتخاب‌شده در فرم را به‌عنوان دسترسی معلم ثبت می‌کند.
     * اگر معلم قبلاً (حتی به‌صورت حذف‌شده) به همین کتاب دسترسی
     * داشته، به‌جای رکورد تکراری، همان فعال می‌شود.
     */
    protected function assignBook(User $teacher): void
    {
        if (! $this->bookIdToAssign) {
            return;
        }

        TeacherAssignment::withTrashed()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'book_id' => $this->bookIdToAssign,
            ],
            [
                'assigned_by' => auth()->id(),
                'commission_percentage' => $this->commissionPercentageToAssign,
                'is_active' => true,
                'deleted_at' => null,
            ]
        );
    }

    protected function getRedirectUrl(): string
    {
        return TeacherResource::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'معلم با موفقیت ایجاد شد.';
    }
}
