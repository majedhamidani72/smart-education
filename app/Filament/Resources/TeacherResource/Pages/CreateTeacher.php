<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

/**
 * ایجاد معلم
 * --------------------------------------------------------------------
 * این فرم فقط حساب کاربری معلم را می‌سازد. مدیریت کتاب‌های
 * تدریسی (که می‌تواند چندتا و از پایه‌های مختلف باشد) کاملاً
 * جدا شده و بعد از ساخت، در تب «کتاب‌های تدریسی» صفحه‌ی ویرایش
 * همین معلم انجام می‌شود — قبلاً چون این فرم خودش هم یک کتاب
 * می‌گرفت، انتخاب کتاب دوم برای یک معلم موجود، کتاب اول را از
 * دید مخفی می‌کرد (چون فقط آخرین تخصیص نمایش داده می‌شد).
 */
class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected ?User $deletedUser = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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

            Notification::make()
                ->title('معلم حذف‌شده بازیابی شد. برای مدیریت کتاب‌هایش وارد صفحه‌ی ویرایش او شو.')
                ->success()
                ->send();

            $this->redirect(
                TeacherResource::getUrl('edit', ['record' => $this->deletedUser])
            );

            $this->halt();
        }

        // کاربر فعالِ موجود با همین موبایل — به‌جای رکورد جدید،
        // همین حساب به‌روزرسانی می‌شود.
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

            Notification::make()
                ->title('حساب موجود با همین شماره، به معلم تبدیل شد. برای مدیریت کتاب‌هایش وارد صفحه‌ی ویرایش او شو.')
                ->success()
                ->send();

            $this->redirect(
                TeacherResource::getUrl('edit', ['record' => $existingActiveUser])
            );

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
    }

    /**
     * بعد از ساخت حساب، مستقیم به صفحه‌ی ویرایش همین معلم می‌رویم
     * (نه لیست) — چون قدم بعدی طبیعی، اضافه‌کردن کتاب(ها) از تب
     * «کتاب‌های تدریسی» است.
     */
    protected function getRedirectUrl(): string
    {
        return TeacherResource::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'معلم با موفقیت ایجاد شد. حالا از تب «کتاب‌های تدریسی» کتاب(ها)ش رو اضافه کن.';
    }
}
