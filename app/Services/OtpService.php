<?php

namespace App\Services;

use Throwable;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use App\Services\Sms\SmsService;
use App\Exceptions\Auth\ExpiredOtpException;
use App\Exceptions\Auth\InvalidLoginTokenException;
use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\OtpAlreadyUsedException;
use App\Exceptions\Auth\OtpAttemptsExceededException;
use App\Repositories\Interfaces\OtpCodeRepositoryInterface;

class OtpService
{
    /**
     * Repository مربوط به OTP
     */
    protected OtpCodeRepositoryInterface $otpRepository;

    /**
     * سرویس ارسال پیامک
     */
    protected SmsService $smsService;

    public function __construct(
        OtpCodeRepositoryInterface $otpRepository,
        SmsService $smsService
    ) {
        $this->otpRepository = $otpRepository;

        $this->smsService = $smsService;
    }

    /**
     * تولید کد OTP
     */
    protected function generateOtp(): string
    {
        return (string) random_int(
            100000,
            999999
        );
    }

    /**
     * تولید Login Token
     */
    protected function generateLoginToken(): string
    {
        return Str::random(64);
    }

    /**
     * ارسال کد تایید
     */
    public function sendOtp(
        string $mobile,
        string $purpose = 'login'
    ): string
    {
        try {

            $this->otpRepository
                ->deactivateActiveOtps(
                    $mobile,
                    $purpose
                );

            $otp = $this->generateOtp();

            $loginToken = $this->generateLoginToken();

            $this->otpRepository->create([

                'mobile' => $mobile,

                'code' => $otp,

                'login_token' => $loginToken,

                'purpose' => $purpose,

                'attempts' => 0,

                'is_verified' => false,

                'expires_at' => now()->addMinutes(2),

                'ip_address' => request()->ip(),

                'user_agent' => request()->userAgent(),

            ]);

            $sent = $this->smsService->sendOtp(

                $mobile,

                $otp

            );

            if (! $sent) {

                throw new RuntimeException('SMS OTP could not be sent.');

            }

            return $loginToken;

        } catch (Throwable $e) {

            Log::error('Send OTP failed.', [

                'mobile' => $mobile,

                'purpose' => $purpose,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * تایید کد OTP
     */
    public function verifyOtp(
        string $loginToken,
        string $code
    ): User
    {
        try {

            $otp = $this->otpRepository
                ->findByLoginToken(
                    $loginToken
                );

            if (!$otp) {

                throw new InvalidLoginTokenException();

            }

            $otp->refresh();

            if ($otp->attempts >= 5) {

                throw new OtpAttemptsExceededException();

            }

            if ($otp->isExpired()) {

                throw new ExpiredOtpException();

            }

            if ($otp->isUsed()) {

                throw new OtpAlreadyUsedException();

            }

            if ($otp->code !== $code) {

                $otp->incrementAttempts();

                throw new InvalidOtpException();

            }

            $user = User::firstOrCreate(

                [

                    'mobile' => $otp->mobile,

                ],

                [

                    'phone_verified_at' => now(),

                ]

            );

            // اگر همین الان برای اولین‌بار ساخته شده، نقش «دانش‌آموز»
            // بهش داده می‌شود — بدون این نقش، تمام مجوزهایی که به
            // دانش‌آموز تعلق دارند (مثل purchases.create) کار
            // نمی‌کنند، چون سیستم مجوزها کاملاً بر پایه‌ی نقش است.
            if ($user->wasRecentlyCreated) {

                $user->assignRole('Student');

            }

            $user->update([

                'phone_verified_at' => now(),

                'last_login_at' => now(),

            ]);

            $otp->markAsVerified();

            $otp->user_id = $user->id;

            $otp->attempts = 0;

            $otp->save();

            return $user;

        } catch (Throwable $e) {

            Log::error('Verify OTP failed.', [

                'login_token' => $loginToken,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }

    /**
     * ارسال مجدد کد تایید
     */
    public function resendOtp(
        string $loginToken
    ): array
    {
        try {

            $otp = $this->otpRepository
                ->findByLoginToken(
                    $loginToken
                );

            if (!$otp) {

                throw new InvalidLoginTokenException();

            }

            if ($otp->isUsed()) {

                throw new OtpAlreadyUsedException();

            }

            $otp->markAsUsed();

            $newOtp = $this->generateOtp();

            $newLoginToken = $this->generateLoginToken();

            $this->otpRepository->create([

                'user_id' => $otp->user_id,

                'mobile' => $otp->mobile,

                'code' => $newOtp,

                'login_token' => $newLoginToken,

                'purpose' => $otp->purpose,

                'attempts' => 0,

                'is_verified' => false,

                'expires_at' => now()->addMinutes(2),

                'ip_address' => request()->ip(),

                'user_agent' => request()->userAgent(),

            ]);

            $sent = $this->smsService->sendOtp(

                $otp->mobile,

                $newOtp

            );

            if (! $sent) {

                throw new RuntimeException('SMS OTP could not be sent.');

            }

            return [

                'login_token' => $newLoginToken,

                'message' => 'کد تایید مجدداً ارسال شد.',

            ];

        } catch (Throwable $e) {

            Log::error('Resend OTP failed.', [

                'login_token' => $loginToken,

                'error' => $e->getMessage(),

            ]);

            throw $e;

        }
    }
}
