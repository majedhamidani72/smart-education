<?php

namespace App\Policies;

use App\Models\OtpCode;
use App\Models\User;

class OtpCodePolicy
{
    /**
     * مشاهده لیست OTP
     */
    public function viewAny(
        User $user
    ): bool
    {
        return $user->can('otp-codes.view');
    }

    /**
     * مشاهده OTP
     */
    public function view(
        User $user,
        OtpCode $otpCode
    ): bool
    {
        return $user->can('otp-codes.view');
    }

    /**
     * ایجاد OTP
     */
    public function create(
        User $user
    ): bool
    {
        return $user->can('otp-codes.create');
    }

    /**
     * بروزرسانی OTP
     */
    public function update(
        User $user,
        OtpCode $otpCode
    ): bool
    {
        return $user->can('otp-codes.update');
    }

    /**
     * حذف OTP
     */
    public function delete(
        User $user,
        OtpCode $otpCode
    ): bool
    {
        return $user->can('otp-codes.delete');
    }

    /**
     * بازیابی
     */
    public function restore(
        User $user,
        OtpCode $otpCode
    ): bool
    {
        return $this->update(
            $user,
            $otpCode
        );
    }

    /**
     * حذف دائمی
     */
    public function forceDelete(
        User $user,
        OtpCode $otpCode
    ): bool
    {
        return $this->delete(
            $user,
            $otpCode
        );
    }
}
