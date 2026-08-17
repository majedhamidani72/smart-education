<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    // اگر کاربری با همین موبایل قبلاً حذف نرم‌افزاری شده باشد،
    // به‌جای رکورد جدید، همان بازیابی می‌شود.
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

            // نقش ادمین دوباره تضمین می‌شود، حتی اگر قبلاً نقش
            // دیگری داشته باشد.
            $this->deletedUser->syncRoles(['Admin']);

            Notification::make()
                ->title('ادمین حذف‌شده بازیابی شد.')
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
        // نقش «Admin» همیشه به‌صورت خودکار و ثابت اختصاص داده
        // می‌شود؛ در این فرم هیچ انتخاب نقشی از کاربر گرفته نشده.
        $this->record->assignRole('Admin');
    }

    protected function getRedirectUrl(): string
    {
        return AdminResource::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'ادمین با موفقیت ایجاد شد.';
    }
}
