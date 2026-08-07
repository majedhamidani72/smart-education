<?php

namespace App\Repositories\Interfaces;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;

interface DeviceRepositoryInterface
{
    /**
     * ایجاد دستگاه جدید
     */
    public function create(
        array $data
    ): Device;

    /**
     * بروزرسانی دستگاه
     */
    public function update(
        Device $device,
        array $data
    ): bool;

    /**
     * حذف دستگاه
     */
    public function delete(
        Device $device
    ): bool;

    /**
     * دریافت دستگاه بر اساس شناسه
     */
    public function findById(
        int $id
    ): ?Device;

    /**
     * دریافت دستگاه بر اساس شناسه یکتا
     */
    public function findByIdentifier(
        string $identifier
    ): ?Device;

    /**
     * دریافت تمام دستگاه‌های کاربر
     */
    public function getUserDevices(
        int $userId
    ): Collection;

    /**
     * دریافت دستگاه‌های فعال کاربر
     */
    public function getActiveUserDevices(
        int $userId
    ): Collection;

    /**
     * غیرفعال کردن یک دستگاه
     */
    public function deactivate(
        Device $device
    ): bool;

    /**
     * فعال کردن یک دستگاه
     */
    public function activate(
        Device $device
    ): bool;

    /**
     * بروزرسانی آخرین ورود
     */
    public function updateLastLogin(
        Device $device,
        ?string $ip
    ): bool;

    /**
     * بروزرسانی FCM Token
     */
    public function updateFcmToken(
        Device $device,
        ?string $token
    ): bool;
}
