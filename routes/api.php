<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\GradeController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\ChapterController;
use App\Http\Controllers\Api\V1\SectionController;
use App\Http\Controllers\Api\V1\ContentItemController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\Api\V1\PdfFileController;

Route::prefix('v1')->group(function () {

    // Grades
    Route::get('/grades', [GradeController::class, 'index']);
    Route::get('/grades/{grade}', [GradeController::class, 'show']);
    Route::post('/grades', [GradeController::class, 'store']);
    Route::put('/grades/{grade}', [GradeController::class, 'update']);
    Route::delete('/grades/{grade}', [GradeController::class, 'destroy']);

    // Subjects
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
    Route::post('/subjects', [SubjectController::class, 'store']);
    Route::put('/subjects/{subject}', [SubjectController::class, 'update']);
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);

    // Books
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show']);
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{book}', [BookController::class, 'update']);
    Route::delete('/books/{book}', [BookController::class, 'destroy']);

    // Chapters
    Route::get('/chapters', [ChapterController::class, 'index']);
    Route::get('/chapters/{chapter}', [ChapterController::class, 'show']);
    Route::post('/chapters', [ChapterController::class, 'store']);
    Route::put('/chapters/{chapter}', [ChapterController::class, 'update']);
    Route::delete('/chapters/{chapter}', [ChapterController::class, 'destroy']);

    Route::get('/sections', [SectionController::class, 'index']);
    Route::get('/sections/{section}', [SectionController::class, 'show']);
    Route::post('/sections', [SectionController::class, 'store']);
    Route::put('/sections/{section}', [SectionController::class, 'update']);
    Route::delete('/sections/{section}', [SectionController::class, 'destroy']);

    Route::get('/content-items', [ContentItemController::class, 'index']);
    Route::get('/content-items/{contentItem}', [ContentItemController::class, 'show']);
    Route::post('/content-items', [ContentItemController::class, 'store']);
    Route::put('/content-items/{contentItem}', [ContentItemController::class, 'update']);
    Route::delete('/content-items/{contentItem}', [ContentItemController::class, 'destroy']);
    Route::patch('/content-items/{contentItem}/submit', [ContentItemController::class, 'submitForReview']);
    Route::patch('/content-items/{contentItem}/approve', [ContentItemController::class, 'approve']);
    Route::patch('/content-items/{contentItem}/reject', [ContentItemController::class, 'reject']);
    Route::patch('/content-items/{contentItem}/publish', [ContentItemController::class, 'publish']);

    Route::get('/videos', [VideoController::class, 'index']);
    Route::post('/videos', [VideoController::class, 'store']);
    Route::get('/videos/{video}', [VideoController::class, 'show']);
    Route::put('/videos/{video}', [VideoController::class, 'update']);
    Route::delete('/videos/{video}', [VideoController::class, 'destroy']);

    Route::apiResource('pdf-files', PdfFileController::class);


});
