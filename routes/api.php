<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\GradeController;

Route::prefix('v1')->group(function () {

    Route::get('/grades',[GradeController::class, 'index']);
    Route::get('/grades/{grade}',[GradeController::class, 'show']);
    Route::post('/grades',[GradeController::class, 'store']);
    Route::put('/grades/{grade}',[GradeController::class, 'update']);
    Route::delete('/grades/{grade}',[GradeController::class, 'destroy']);




});
