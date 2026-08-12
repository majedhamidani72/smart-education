<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\User;

class DevicePolicy
{
    /**
     * مشاهده لیست دستگاه‌ها
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('devices.view');
    }

    /**
     * مشاهده یک دستگاه
     */
    public function view(
        User $user,
        Device $device
    ): bool
    {
        return $user->can('devices.view')
            && $device->user_id === $user->id;
    }

    /**
     * ثبت دستگاه
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('devices.create');
    }

    /**
     * بروزرسانی دستگاه
     */
    public function update(
        User $user,
        Device $device
    ): bool
    {
        return $user->can('devices.update')
            && $device->user_id === $user->id;
    }

    /**
     * حذف دستگاه
     */
    public function delete(
        User $user,
        Device $device
    ): bool
    {
        return $user->can('devices.delete')
            && $device->user_id === $user->id;
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        Device $device
    ): bool
    {
        return $this->update(
            $user,
            $device
        );
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        Device $device
    ): bool
    {
        return $this->delete(
            $user,
            $device
        );
    }
}
