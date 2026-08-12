<?php

namespace App\Repositories\Eloquent;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\DeviceRepositoryInterface;

class DeviceRepository extends BaseRepository implements DeviceRepositoryInterface
{
    public function __construct(
        Device $model
    ) {
        parent::__construct($model);
    }

    /**
     * دریافت دستگاه با شناسه یکتا
     */
    public function findByIdentifier(
        string $identifier
    ): ?Device
    {
        return $this->model
            ->where(
                'device_identifier',
                $identifier
            )
            ->first();
    }

    /**
     * تمام دستگاه‌های کاربر
     */
    public function getUserDevices(
        int $userId
    ): Collection
    {
        return $this->model
            ->where(
                'user_id',
                $userId
            )
            ->latest()
            ->get();
    }

    /**
     * دستگاه‌های فعال کاربر
     */
    public function getActiveUserDevices(
        int $userId
    ): Collection
    {
        return $this->model
            ->active()
            ->where(
                'user_id',
                $userId
            )
            ->latest()
            ->get();
    }

    /**
     * فعال کردن دستگاه
     */
    public function activate(
        Device $device
    ): bool
    {
        return $device->update([

            'is_active'     => true,

            'last_login_at' => now(),

        ]);
    }

    /**
     * غیرفعال کردن دستگاه
     */
    public function deactivate(
        Device $device
    ): bool
    {
        return $device->update([

            'is_active' => false,

        ]);
    }

    /**
     * بروزرسانی آخرین ورود
     */
    public function updateLastLogin(
        Device $device,
        ?string $ip
    ): bool
    {
        return $device->update([

            'last_login_at' => now(),

            'last_ip'       => $ip,

        ]);
    }

    /**
     * بروزرسانی FCM Token
     */
    public function updateFcmToken(
        Device $device,
        ?string $token
    ): bool
    {
        return $device->update([

            'fcm_token' => $token,

        ]);
    }
}
