<?php

namespace App\Services;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

class AuthService
{
    /**
     * سرویس OTP
     */
    protected OtpService $otpService;

    /**
     * Constructor
     */
    public function __construct(
        OtpService $otpService
    ) {
        $this->otpService = $otpService;
    }

    /**
     * ارسال کد تایید
     */
    public function sendOtp(
        string $mobile
    ): array {

        $loginToken = $this->otpService->sendOtp(

            $mobile,

            'login'

        );

        return [

            'login_token' => $loginToken,

            'message' => 'کد تایید با موفقیت ارسال شد.',

        ];
    }

    /**
     * تایید کد OTP
     */
    public function verifyOtp(
        string $loginToken,
        string $code
    ): NewAccessToken {

        $user = $this->otpService->verifyOtp(

            $loginToken,

            $code

        );

        /*
        |--------------------------------------------------------------------------
        | حذف تمام توکن‌های قبلی (اختیاری)
        |--------------------------------------------------------------------------
        */

        // $user->tokens()->delete();

        /*
        |--------------------------------------------------------------------------
        | ایجاد Access Token
        |--------------------------------------------------------------------------
        */

        return $user->createToken(

            'mobile-app'

        );
    }

    /**
     * ارسال مجدد کد تایید
     */
    public function resendOtp(
        string $loginToken
    ): array {

        return $this->otpService->resendOtp(

            $loginToken

        );

    }

    /**
     * خروج از حساب
     */
    public function logout(
        User $user
    ): void {

        $user->currentAccessToken()?->delete();

    }

    /**
     * خروج از تمام دستگاه‌ها
     */
    public function logoutFromAllDevices(
        User $user
    ): void {

        $user->tokens()->delete();

    }
}
