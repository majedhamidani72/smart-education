<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use App\Exceptions\Auth\ExpiredOtpException;
use App\Exceptions\Auth\InvalidLoginTokenException;
use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\OtpAlreadyUsedException;
use App\Exceptions\Auth\OtpAttemptsExceededException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * بازیابی رمز عبور برای کاربران پنل (معلم/ادمین/سوپرادمین).
 * --------------------------------------------------------------------
 * چون این کاربران ایمیل ندارند (فقط موبایل)، بازیابی رمز از طریق
 * پیامک OTP انجام می‌شود — با همان زیرساخت OtpService که برای
 * ورود دانش‌آموزان ساخته شده، فقط با purpose جداگانه
 * ('password_reset') تا با کدهای ورود دانش‌آموز قاطی نشود.
 *
 * فرآیند دو مرحله‌ای:
 * ۱. وارد کردن شماره موبایل → دریافت کد پیامکی
 * ۲. وارد کردن کد + رمز عبور جدید → تغییر رمز
 */
class PasswordResetController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'digits:11', 'regex:/^09[0-9]{9}$/'],
        ], [
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
            'mobile.regex' => 'فرمت شماره موبایل صحیح نیست.',
        ]);

        $user = User::where('mobile', $data['mobile'])
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['SuperAdmin', 'Admin', 'Teacher']))
            ->first();

        // عمداً حتی اگر شماره ثبت نشده باشد، همان پیام موفقیت‌آمیز
        // نشان داده می‌شود — تا از طریق این فرم نتوان فهمید کدام
        // شماره‌ها عضو پنل هستند (به‌همان دلیل امنیتی که در فرم
        // ورود پیام خطا مبهم نگه داشته شده).
        if (! $user) {

            return back()->with(
                'status',
                'اگر این شماره در سیستم ثبت باشد، کد تایید برایش پیامک می‌شود.'
            );
        }

        try {

            $loginToken = $this->otpService->sendOtp(
                $data['mobile'],
                'password_reset'
            );

        } catch (Throwable $e) {

            return back()->withErrors([
                'mobile' => 'ارسال پیامک با خطا مواجه شد. کمی بعد دوباره تلاش کنید.',
            ]);
        }

        session([
            'password_reset_token' => $loginToken,
            'password_reset_mobile' => $data['mobile'],
        ]);

        return redirect()->route('password.reset.form');
    }

    public function showResetForm(): View|RedirectResponse
    {
        if (! session('password_reset_token')) {

            return redirect()
                ->route('password.forgot.form')
                ->withErrors(['mobile' => 'ابتدا شماره موبایل خود را وارد کنید.']);
        }

        return view('auth.reset-password', [
            'mobile' => session('password_reset_mobile'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $loginToken = session('password_reset_token');

        if (! $loginToken) {

            return redirect()
                ->route('password.forgot.form')
                ->withErrors(['mobile' => 'نشست بازیابی رمز منقضی شده — دوباره شروع کنید.']);
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'code.required' => 'کد تایید را وارد کنید.',
            'code.digits' => 'کد تایید باید ۶ رقم باشد.',
            'password.required' => 'رمز عبور جدید را وارد کنید.',
            'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        ]);

        try {

            $user = $this->otpService->verifyOtp(
                $loginToken,
                $data['code']
            );

        } catch (InvalidOtpException $e) {

            return back()->withErrors(['code' => 'کد تایید اشتباه است.']);

        } catch (ExpiredOtpException $e) {

            return back()->withErrors(['code' => 'کد تایید منقضی شده — دوباره شروع کنید.']);

        } catch (OtpAlreadyUsedException|InvalidLoginTokenException $e) {

            return redirect()
                ->route('password.forgot.form')
                ->withErrors(['mobile' => 'نشست بازیابی رمز نامعتبر است — دوباره شروع کنید.']);

        } catch (OtpAttemptsExceededException $e) {

            return back()->withErrors(['code' => 'تعداد تلاش‌های مجاز تمام شد — دوباره شروع کنید.']);
        }

        $user->update([
            'password' => $data['password'],
            'must_change_password' => false,
        ]);

        session()->forget(['password_reset_token', 'password_reset_mobile']);

        return redirect()
            ->route('filament.admin.auth.login')
            ->with('status', 'رمز عبور با موفقیت تغییر کرد — حالا وارد شوید.');
    }
}
