<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgreementController;

/*
|--------------------------------------------------------------------------
| Agreement
|--------------------------------------------------------------------------
*/

Route::middleware(['auth',])->group(function () {
    Route::get('/agreement', [AgreementController::class, 'show'])->name('agreement.show');
    Route::post('/agreement', [AgreementController::class, 'accept'])->name('agreement.accept');
});
