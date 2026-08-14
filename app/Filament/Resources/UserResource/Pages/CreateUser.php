<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?User $deletedUser = null;


    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {

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

                'must_change_password' =>
                $data['must_change_password'] ?? false,

                'is_active' =>
                $data['is_active'] ?? true,

            ]);


            if (
                isset($data['roles'])
                &&
                filled($data['roles'])
            ) {

                $this->deletedUser->syncRoles(
                    $data['roles']
                );
            }


            Notification::make()
                ->title('کاربر حذف شده بازیابی شد.')
                ->success()
                ->send();


            $this->halt();
        }



        if (
            isset($data['password'])
            &&
            filled($data['password'])
        ) {

            $data['password'] = Hash::make(
                $data['password']
            );
        }



        unset($data['roles']);


        return $data;
    }



    protected function afterCreate(): void
    {
        if (
            isset($this->data['roles'])
            &&
            filled($this->data['roles'])
        ) {

            $this->record->syncRoles(
                $this->data['roles']
            );
        }
    }



    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }



    protected function getCreatedNotificationTitle(): ?string
    {
        return 'کاربر با موفقیت ایجاد شد.';
    }
}
