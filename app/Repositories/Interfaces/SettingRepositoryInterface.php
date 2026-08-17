<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Setting;

/**
 * @extends BaseRepositoryInterface<Setting>
 */
interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * دریافت تنظیم بر اساس کلید
     */
    public function findByKey(
        string $key
    ): ?Setting;

    /**
     * دریافت مقدار تنظیم
     */
    public function getValue(
        string $key,
        mixed $default = null
    ): mixed;

    /**
     * ایجاد یا بروزرسانی تنظیم
     */
    public function setValue(
        string $key,
        mixed $value,
        ?string $description = null
    ): Setting;

    /**
     * بررسی وجود تنظیم
     */
    public function exists(
        string $key
    ): bool;

    /**
     * حذف تنظیم
     */
    public function deleteByKey(
        string $key
    ): bool;
}
