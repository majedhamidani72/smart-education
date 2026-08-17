<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Setting;
use App\Repositories\Interfaces\SettingRepositoryInterface;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(
        Setting $model
    ) {
        parent::__construct($model);
    }

    /**
     * دریافت تنظیم بر اساس کلید
     */
    public function findByKey(
        string $key
    ): ?Setting {

        return $this->query()

            ->where('key', $key)

            ->first();

    }

    /**
     * دریافت مقدار تنظیم
     */
    public function getValue(
        string $key,
        mixed $default = null
    ): mixed {

        return $this->query()

            ->where('key', $key)

            ->value('value')

            ?? $default;

    }

    /**
     * ایجاد یا بروزرسانی تنظیم
     */
    public function setValue(
        string $key,
        mixed $value,
        ?string $description = null
    ): Setting {

        return Setting::updateOrCreate(

            [
                'key' => $key,
            ],

            [
                'value' => $value,
                'description' => $description,
            ]

        );

    }

    /**
     * بررسی وجود تنظیم
     */
    public function exists(
        string $key
    ): bool {

        return $this->query()

            ->where('key', $key)

            ->exists();

    }

    /**
     * حذف تنظیم
     */
    public function deleteByKey(
        string $key
    ): bool {

        return (bool) $this->query()

            ->where('key', $key)

            ->delete();

    }
}
