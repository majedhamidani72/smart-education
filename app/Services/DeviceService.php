<?php

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use App\Repositories\Interfaces\DeviceRepositoryInterface;

class DeviceService
{
    /**
     * Repository دستگاه
     */
    protected DeviceRepositoryInterface $deviceRepository;

    /**
     * Constructor
     */
    public function __construct(
        DeviceRepositoryInterface $deviceRepository
    ) {
        $this->deviceRepository = $deviceRepository;
    }

    /**
     * ثبت یا بروزرسانی دستگاه
     */
    public function registerDevice(
        User $user,
        array $data
    ): Device {

        $device = $this->deviceRepository
            ->findByIdentifier(
                $data['device_identifier']
            );

        /*
        |--------------------------------------------------------------------------
        | اگر دستگاه قبلاً ثبت شده باشد
        |--------------------------------------------------------------------------
        */

        if ($device) {

            $this->deviceRepository->update(

                $device,

                [

                    'user_id' => $user->id,

                    'device_name' => $data['device_name'],

                    'manufacturer' => $data['manufacturer'] ?? null,

                    'model' => $data['model'] ?? null,

                    'platform' => $data['platform'],

                    'os_version' => $data['os_version'] ?? null,

                    'app_version' => $data['app_version'] ?? null,

                    'fcm_token' => $data['fcm_token'] ?? null,

                    'last_ip' => request()->ip(),

                    'last_login_at' => now(),

                    'is_active' => true,

                ]

            );

            return $device->fresh();
        }

        /*
        |--------------------------------------------------------------------------
        | ثبت دستگاه جدید
        |--------------------------------------------------------------------------
        */

        return $this->deviceRepository->create([

            'user_id' => $user->id,

            'device_identifier' => $data['device_identifier'],

            'device_name' => $data['device_name'],

            'manufacturer' => $data['manufacturer'] ?? null,

            'model' => $data['model'] ?? null,

            'platform' => $data['platform'],

            'os_version' => $data['os_version'] ?? null,

            'app_version' => $data['app_version'] ?? null,

            'fcm_token' => $data['fcm_token'] ?? null,

            'last_ip' => request()->ip(),

            'last_login_at' => now(),

            'is_active' => true,

        ]);
    }

    /**
     * دریافت دستگاه‌های کاربر
     */
    public function getUserDevices(
        User $user
    ) {
        return $this->deviceRepository
            ->getUserDevices(
                $user->id
            );
    }

    /**
     * دریافت دستگاه‌های فعال
     */
    public function getActiveDevices(
        User $user
    ) {
        return $this->deviceRepository
            ->getActiveUserDevices(
                $user->id
            );
    }

    /**
     * غیرفعال کردن یک دستگاه
     */
    public function deactivateDevice(
        Device $device
    ): bool {

        return $this->deviceRepository
            ->deactivate(
                $device
            );
    }

    /**
     * فعال کردن دستگاه
     */
    public function activateDevice(
        Device $device
    ): bool {

        return $this->deviceRepository
            ->activate(
                $device
            );
    }

    /**
     * بروزرسانی FCM Token
     */
    public function updateFcmToken(
        Device $device,
        ?string $token
    ): bool {

        return $this->deviceRepository
            ->updateFcmToken(
                $device,
                $token
            );
    }

    /**
     * بروزرسانی آخرین ورود
     */
    public function updateLastLogin(
        Device $device
    ): bool {

        return $this->deviceRepository
            ->updateLastLogin(
                $device,
                request()->ip()
            );
    }


    /**
     * بروزرسانی اطلاعات دستگاه
     */
    public function updateDevice(
        Device $device,
        array $data
    ): bool {

        return $this->deviceRepository
            ->update(

                $device,

                [

                    'device_name' => $data['device_name'] ?? $device->device_name,

                    'manufacturer' => $data['manufacturer'] ?? $device->manufacturer,

                    'model' => $data['model'] ?? $device->model,

                    'platform' => $data['platform'] ?? $device->platform,

                    'os_version' => $data['os_version'] ?? $device->os_version,

                    'app_version' => $data['app_version'] ?? $device->app_version,

                    'fcm_token' => $data['fcm_token'] ?? $device->fcm_token,

                    'is_active' => $data['is_active'] ?? $device->is_active,

                ]

            );
    }
}
