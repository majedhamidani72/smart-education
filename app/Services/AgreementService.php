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

        return match ($type) {

            'teacher' => $this->settingService->teacherAgreement(),

            'admin' => $this->settingService->adminAgreement(),

            default => null,

        };

    }

    public function getAgreementVersion(
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

            TeacherAgreement::removeOldVersions(

                $user->id,

                $type,

                $version

            );

            $agreement = TeacherAgreement::accept(

                $user->id,

                $type,

                $version,

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
