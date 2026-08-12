<?php

namespace App\Repositories\Interfaces;

use App\Models\OtpCode;

interface OtpCodeRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * دریافت OTP بر اساس Login Token
     */
    public function findByLoginToken(
        string $loginToken
    ): ?OtpCode;

    /**
     * دریافت OTP فعال
     */
    public function getActiveOtp(
        string $mobile,
        string $purpose
    ): ?OtpCode;

    /**
     * غیرفعال کردن OTP های فعال
     */
    public function deactivateActiveOtps(
        string $mobile,
        string $purpose
    ): void;
}
