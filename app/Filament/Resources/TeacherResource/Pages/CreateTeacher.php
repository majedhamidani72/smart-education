<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\TeacherAssignment;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

/**
 * ایجاد معلم
 * --------------------------------------------------------------------
 * این فرم حساب کاربری معلم را می‌سازد و به‌صورت اختیاری، یک کتاب
 * اول هم همین‌جا می‌گیرد (برای این‌که رایج‌ترین حالت — معلم تازه
 * با یک کتاب — همه‌چیزش توی یک صفحه انجام بشود). برای کتاب دوم
 * به بعد، باید از تب «کتاب‌های تدریسی» صفحه‌ی ویرایش همین معلم
 * استفاده کرد — چون قبلاً وقتی این فرم خودش هم یک کتاب می‌گرفت،
 * انتخاب کتاب دوم برای یک معلم موجود، کتاب اول را از دید مخفی
 * می‌کرد (چون فقط آخرین تخصیص نمایش داده می‌شد).
 */
class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected ?User $deletedUser = null;

    protected ?int $firstBookId = null;

    protected int $firstCommissionPercentage = 60;

    protected ?string $plainPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // فیلدهای مربوط به «کتاب اول» فقط کمکی‌اند و روی خودِ
        // User ذخیره نمی‌شوند — باید قبل از ساخت کاربر برداشته
        // شوند.
        $this->firstBookId = $data['first_book_id'] ?? null;

        $this->firstCommissionPercentage = $data['first_commission_percentage'] ?? 60;

        // برای پیامک «رمز اولیه» باید مقدار خامِ رمز (قبل از
        // هش‌شدن) نگه داشته شود.
        $this->plainPassword = $data['password'] ?? null;

        unset(
            $data['first_app_id'],
            $data['first_grade_id'],
            $data['first_subject_id'],
            $data['first_book_id'],
            $data['first_commission_percentage'],
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

            $this->assignFirstBookIfProvided($this->deletedUser);

            $this->sendAccountCreatedSms($this->deletedUser);

            Notification::make()
                ->title('معلم حذف‌شده بازیابی شد. برای کتاب‌های بیشتر، وارد صفحه‌ی ویرایش او شو.')
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

            $this->assignFirstBookIfProvided($existingActiveUser);

            $this->sendAccountCreatedSms($existingActiveUser);

            Notification::make()
                ->title('حساب موجود با همین شماره، به معلم تبدیل شد. برای کتاب‌های بیشتر، وارد صفحه‌ی ویرایش او شو.')
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

        $this->assignFirstBookIfProvided($this->record);

        $this->sendAccountCreatedSms($this->record);
    }

    /**
     * پیامک ساخت حساب — شامل شماره‌موبایل (که همان نام کاربری
     * ورودشان است) و رمز اولیه، تا حتی اگر خودشان هنوز پنل را
     * باز نکرده باشند، بدانند چطور وارد شوند.
     */
    protected function sendAccountCreatedSms(User $teacher): void
    {
        if (! $teacher->mobile || ! $this->plainPassword) {
            return;
        }

        \App\Jobs\SendSmsJob::dispatch(
            $teacher->mobile,
            app(\App\Services\SettingService::class)->smsText('sms_account_created_teacher', [
                'password' => $this->plainPassword,
            ])
        );
    }

    /**
     * اگر «کتاب اول» توی فرم پر شده باشد، همین‌جا برای معلم ثبت
     * می‌شود.
     */
    protected function assignFirstBookIfProvided(User $teacher): void
    {
        if (! $this->firstBookId) {
            return;
        }

        TeacherAssignment::updateOrCreate(

            [
                'teacher_id' => $teacher->id,
                'book_id' => $this->firstBookId,
            ],

            [
                'assigned_by' => auth()->id(),
                'commission_percentage' => $this->firstCommissionPercentage,
                'is_active' => true,
            ]

        );
    }

    /**
     * بعد از ساخت حساب، مستقیم به صفحه‌ی ویرایش همین معلم می‌رویم
     * (نه لیست) — تا اگه خواست کتاب بیشتری اضافه کند، همان‌جا
     * تب «کتاب‌های تدریسی» در دسترسش باشد.
     */
    protected function getRedirectUrl(): string
    {
        return TeacherResource::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'معلم با موفقیت ایجاد شد. برای کتاب‌های بیشتر، از تب «کتاب‌های تدریسی» استفاده کن.';
    }
}
