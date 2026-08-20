<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SettingService
{
    public function __construct(
        protected SettingRepositoryInterface $settingRepository
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Read
    |--------------------------------------------------------------------------
    */

    public function getAll(): Collection
    {
        return $this->settingRepository->getAll();
    }

    public function findByKey(
        string $key
    ): ?Setting {

        return $this->settingRepository->findByKey($key);

    }

    public function getValue(
        string $key,
        mixed $default = null
    ): mixed {

        return $this->settingRepository->getValue(
            $key,
            $default
        );

    }

    public function exists(
        string $key
    ): bool {

        return $this->settingRepository->exists($key);

    }

    /*
    |--------------------------------------------------------------------------
    | Write
    |--------------------------------------------------------------------------
    */

    public function setValue(
        string $key,
        mixed $value,
        ?string $description = null
    ): Setting {

        DB::beginTransaction();

        try {

            $setting = $this->settingRepository->setValue(

                $key,

                $value,

                $description

            );

            DB::commit();

            return $setting;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(
                'Setting save failed.',
                [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;

        }

    }

    public function deleteByKey(
        string $key
    ): bool {

        DB::beginTransaction();

        try {

            $deleted = $this->settingRepository->deleteByKey($key);

            DB::commit();

            return $deleted;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(
                'Setting delete failed.',
                [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;

        }

    }

    /*
    |--------------------------------------------------------------------------
    | General
    |--------------------------------------------------------------------------
    */

    public function appName(): string
    {
        return (string) $this->getValue(
            'app_name',
            'اسمارت اجوکیشن'
        );
    }

    public function appLogo(): ?string
    {
        return $this->getValue('app_logo');
    }

    /*
    |--------------------------------------------------------------------------
    | Agreements
    |--------------------------------------------------------------------------
    */

    public function teacherAgreement(): ?string
    {
        return $this->getValue('teacher_agreement');
    }

    public function teacherAgreementVersion(): string
    {
        return (string) $this->getValue(
            'teacher_agreement_version',
            '1.0'
        );
    }

    public function adminAgreement(): ?string
    {
        return $this->getValue('admin_agreement');
    }

    public function adminAgreementVersion(): string
    {
        return (string) $this->getValue(
            'admin_agreement_version',
            '1.0'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Gateway Fees
    |--------------------------------------------------------------------------
    */

    /**
     * درصد کارمزدی که یک درگاه مشخص از مبلغ فروش کسر می‌کند —
     * پیش از محاسبه‌ی سهم معلم از این عدد استفاده می‌شود.
     */
    public function gatewayFeePercentage(string $gateway): float
    {
        $key = match ($gateway) {

            'bazaar' => 'gateway_fee_bazaar',

            'myket' => 'gateway_fee_myket',

            default => 'gateway_fee_zibal',
        };

        return (float) $this->getValue($key, '1');
    }

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    public function passwordMinLength(): int
    {
        return (int) $this->getValue(
            'password_min_length',
            8
        );
    }

    public function forceChangePassword(): bool
    {
        return (bool) $this->getValue(
            'force_change_password',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Video
    |--------------------------------------------------------------------------
    */

    public function videoMaxSize(): int
    {
        return (int) $this->getValue(
            'video_max_size',
            500
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    */

    public function teacherSharePercent(): int
    {
        return (int) $this->getValue(
            'teacher_share_percent',
            70
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */

    public function androidMinVersion(): string
    {
        return (string) $this->getValue(
            'android_min_version',
            '1.0.0'
        );
    }

    public function forceUpdate(): bool
    {
        return (bool) $this->getValue(
            'force_update',
            false
        );
    }
}
