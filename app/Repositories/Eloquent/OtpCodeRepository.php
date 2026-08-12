<?php

namespace App\Repositories\Eloquent;

use App\Models\OtpCode;
use App\Repositories\Interfaces\OtpCodeRepositoryInterface;

class OtpCodeRepository extends BaseRepository implements OtpCodeRepositoryInterface
{
    public function __construct(
        OtpCode $model
    ) {
        parent::__construct($model);
    }

    /**
     * دریافت OTP بر اساس Login Token
     */
    public function findByLoginToken(
        string $loginToken
    ): ?OtpCode
    {
        return $this->model
            ->where(
                'login_token',
                $loginToken
            )
            ->first();
    }

    /**
     * دریافت OTP فعال
     */
    public function getActiveOtp(
        string $mobile,
        string $purpose
    ): ?OtpCode
    {
        return $this->model
            ->active()
            ->where(
                'mobile',
                $mobile
            )
            ->where(
                'purpose',
                $purpose
            )
            ->latest()
            ->first();
    }

    /**
     * غیرفعال کردن OTP های فعال
     */
    public function deactivateActiveOtps(
        string $mobile,
        string $purpose
    ): void
    {
        $this->model
            ->active()
            ->where(
                'mobile',
                $mobile
            )
            ->where(
                'purpose',
                $purpose
            )
            ->update([

                'used_at' => now(),

            ]);
    }
}
