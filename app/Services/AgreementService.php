<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TeacherAgreement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AgreementService
{
    public function __construct(
        protected SettingService $settingService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Agreement Information
    |--------------------------------------------------------------------------
    */

    public function getAgreementText(
        string $type
    ): ?string {

        $text = match ($type) {

            'teacher' => $this->settingService->teacherAgreement(),

            'admin' => $this->settingService->adminAgreement(),

            default => null,

        };

        // متن قرارداد به‌جای عدد ثابت، یک جای‌گزین دارد تا همیشه
        // با تنظیم واقعی «درصد پیش‌فرض سهم معلم» یکدست بماند —
        // هرجا این تنظیم عوض شود، خودِ متن قرارداد هم بدون نیاز
        // به ویرایش دستی، همان لحظه به‌روز نمایش داده می‌شود.
        if ($text) {

            $text = str_replace(
                '{{TEACHER_DEFAULT_PERCENTAGE}}',
                (string) $this->settingService->defaultTeacherCommissionPercentage(),
                $text
            );
        }

        return $text;

    }

    /**
     * نسخه‌ی مؤثر قرارداد را برمی‌گرداند.
     * --------------------------------------------------------------------
     * به‌جای تکیه بر شماره‌ی نسخه‌ای که مدیر باید دستی توی تنظیمات
     * عوض کند (و ممکن است فراموش شود)، این نسخه به‌صورت خودکار از
     * روی «هش متن قرارداد» ساخته می‌شود. یعنی با هر تغییری در خودِ
     * متن قرارداد — حتی اگر مدیر شماره‌ی نسخه را دستی عوض نکرده
     * باشد — این هش هم عوض می‌شود و در نتیجه پذیرش‌های قبلی دیگر
     * معتبر نیستند و قرارداد دوباره برای معلم/ادمین نمایش داده
     * می‌شود.
     */
    public function getAgreementVersion(
        string $type
    ): string {

        $text = $this->getAgreementText($type);

        if (blank($text)) {
            return '1.0';
        }

        return substr(md5($text), 0, 12);

    }

    /**
     * @deprecated دیگر مستقیم استفاده نمی‌شود؛ نسخه از روی هش متن
     * محاسبه می‌شود (نگاه کنید به getAgreementVersion). این متد
     * فقط برای سازگاری با کدهای قدیمی نگه داشته شده.
     */
    protected function legacyManualVersion(
        string $type
    ): string {

        return match ($type) {

            'teacher' => $this->settingService->teacherAgreementVersion(),

            'admin' => $this->settingService->adminAgreementVersion(),

            default => '1.0',

        };

    }

    /*
    |--------------------------------------------------------------------------
    | Agreement Check
    |--------------------------------------------------------------------------
    */

    public function hasAccepted(
        User $user
    ): bool {

        if ($user->hasRole('SuperAdmin')) {
            return true;
        }

        $type = $this->resolveAgreementType($user);

        $version = $this->getAgreementVersion($type);

        return TeacherAgreement::hasAccepted(

            $user->id,

            $type,

            $version

        );

    }

    /*
    |--------------------------------------------------------------------------
    | Accept Agreement
    |--------------------------------------------------------------------------
    */

    public function accept(
        User $user,
        ?string $ip,
        ?string $userAgent
    ): TeacherAgreement {

        DB::beginTransaction();

        try {

            $type = $this->resolveAgreementType($user);

            $version = $this->getAgreementVersion($type);

            $text = $this->getAgreementText($type);

            TeacherAgreement::removeOldVersions(

                $user->id,

                $type,

                $version

            );

            $agreement = TeacherAgreement::accept(

                $user->id,

                $type,

                $version,

                $text,

                $ip,

                $userAgent

            );

            DB::commit();

            return $agreement;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error(
                'Agreement acceptance failed.',
                [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Agreement Type
    |--------------------------------------------------------------------------
    */

    public function resolveAgreementType(
        User $user
    ): string {

        if (

            $user->hasRole('Admin')

        ) {

            return 'admin';

        }

        return 'teacher';

    }

    public function hasAgreementText(
        User $user
    ): bool {

        if ($user->hasRole('SuperAdmin')) {
            return false;
        }

        return filled(

            $this->getAgreementText(

                $this->resolveAgreementType($user)

            )

        );

    }

    public function latestAgreement(
        User $user
    ): ?TeacherAgreement {

        $type = $this->resolveAgreementType($user);

        return TeacherAgreement::latestAgreement(

            $user->id,

            $type

        );

    }
}
