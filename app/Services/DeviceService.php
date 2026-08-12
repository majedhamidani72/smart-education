<?php

namespace App\Services;

use Throwable;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
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
    ): Device
    {
        try {

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

                $device->refresh();

                return $device;
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

        } catch (Throwable $e) {

            Log::error('Device registration failed.', [

                'user_id' => $user->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * دریافت دستگاه‌های کاربر
     */
    public function getUserDevices(
        User $user
    )
    {
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
    )
    {
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
    ): bool
    {
        try {

            return $this->deviceRepository
                ->deactivate(
                    $device
                );

        } catch (Throwable $e) {

            Log::error('Device deactivation failed.', [

                'device_id' => $device->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * فعال کردن دستگاه
     */
    public function activateDevice(
        Device $device
    ): bool
    {
        try {

            return $this->deviceRepository
                ->activate(
                    $device
                );

        } catch (Throwable $e) {

            Log::error('Device activation failed.', [

                'device_id' => $device->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * بروزرسانی FCM Token
     */
    public function updateFcmToken(
        Device $device,
        ?string $token
    ): bool
    {
        try {

            return $this->deviceRepository
                ->updateFcmToken(
                    $device,
                    $token
                );

        } catch (Throwable $e) {

            Log::error('FCM token update failed.', [

                'device_id' => $device->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * بروزرسانی آخرین ورود
     */
    public function updateLastLogin(
        Device $device
    ): bool
    {
        try {

            return $this->deviceRepository
                ->updateLastLogin(
                    $device,
                    request()->ip()
                );

        } catch (Throwable $e) {

            Log::error('Update last login failed.', [

                'device_id' => $device->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * بروزرسانی اطلاعات دستگاه
     */
    public function updateDevice(
        Device $device,
        array $data
    ): Device
    {
        try {

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

        } catch (Throwable $e) {

            Log::error('Device update failed.', [

                'device_id' => $device->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }
}
