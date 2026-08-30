<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\AgreementPrintController;
use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Agreement
|--------------------------------------------------------------------------
*/

Route::middleware(['auth',])->group(function () {
    Route::get('/agreement', [AgreementController::class, 'show'])->name('agreement.show');
    Route::post('/agreement', [AgreementController::class, 'accept'])->name('agreement.accept');

    Route::get('/admin/agreements/{agreement}/print', [AgreementPrintController::class, 'show'])
        ->name('agreement.print');
});

/*
|--------------------------------------------------------------------------
| بازیابی رمز عبور پنل (معلم/ادمین/سوپرادمین) — با پیامک، نه ایمیل
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('password.')->group(function () {

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])
        ->name('forgot.form');

    Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])
        ->middleware('throttle:send-otp')
        ->name('forgot.send');

    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])
        ->name('reset.form');

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->name('reset.submit');

});
