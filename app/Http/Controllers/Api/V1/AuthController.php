<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\Auth\ResendOtpRequest;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(
        AuthService $authService
    ) {
        $this->authService = $authService;
    }

    /**
     * ارسال کد تایید
     */
    public function sendOtp(
        SendOtpRequest $request
    ) {

        $key = sprintf(

            '%s|%s',

            $request->ip(),

            $request->mobile

        );

        if (

            RateLimiter::tooManyAttempts(

                $key,

                5

            )

        ) {

            return ApiResponse::error(

                'Too many OTP requests. Please try again after 15 minutes.',

                null,

                429

            );
        }

        RateLimiter::hit(

            $key,

            900

        );

        $result = $this->authService->sendOtp(

            $request->mobile

        );

        return ApiResponse::success(

            $result,

            'OTP sent successfully.'

        );
    }

    /**
     * تایید کد OTP
     */
    public function verifyOtp(
        VerifyOtpRequest $request
    ) {

        $token = $this->authService->verifyOtp(

            $request->login_token,

            $request->code

        );

        return ApiResponse::success(

            [

                'access_token' => $token->plainTextToken,

                'token_type' => 'Bearer',

                'user' => new UserResource(
                    $token->accessToken->tokenable
                ),

            ],

            'Login successful.'

        );
    }

    /**
     * اطلاعات کاربر جاری
     */
    public function me(
        Request $request
    ) {

        return ApiResponse::success(

            new UserResource(
                $request->user()
            ),

            'User retrieved successfully.'

        );
    }

    /**
     * خروج از حساب
     */
    public function logout(
        Request $request
    ) {

        $this->authService->logout(

            $request->user()

        );

        return ApiResponse::success(

            null,

            'Logout successful.'

        );
    }


    /**
     * ارسال مجدد کد تایید
     */
    public function resendOtp(
        ResendOtpRequest $request
    ) {

        $result = $this->authService->resendOtp(

            $request->login_token

        );

        return ApiResponse::success(

            $result,

            'OTP resent successfully.'

        );
    }
}
