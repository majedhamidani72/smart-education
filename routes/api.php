<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GradeController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\ChapterController;
use App\Http\Controllers\Api\V1\SectionController;
use App\Http\Controllers\Api\V1\ContentItemController;
use App\Http\Controllers\Api\V1\VideoController;
use App\Http\Controllers\Api\V1\QuizController;
use App\Http\Controllers\Api\V1\QuizAttemptController;
use App\Http\Controllers\Api\V1\QuestionAttemptController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentCallbackController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\PurchaseItemController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\PaymentTransactionController;


Route::prefix('v1')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->controller(AuthController::class)->group(function () {

        Route::post('/send-otp', 'sendOtp')->middleware('throttle:send-otp');

        Route::post('/verify-otp', 'verifyOtp');

        Route::post('/resend-otp', 'resendOtp');


        Route::middleware('auth:sanctum')->group(function () {

            Route::get('/me', 'me');

            Route::post('/logout', 'logout');
        });
    });



    /*
    |--------------------------------------------------------------------------
    | Education
    |--------------------------------------------------------------------------
    */

    Route::apiResource('grades', GradeController::class)
        ->only(['index', 'show']);

    Route::apiResource('subjects', SubjectController::class)
        ->only(['index', 'show']);

    Route::apiResource('books', BookController::class)
        ->only(['index', 'show']);

    Route::apiResource('chapters', ChapterController::class)
        ->only(['index', 'show']);

    Route::apiResource('sections', SectionController::class)
        ->only(['index', 'show']);



    /*
    |--------------------------------------------------------------------------
    | Content Items
    |--------------------------------------------------------------------------
    */

    Route::prefix('content-items')->controller(ContentItemController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/{contentItem}', 'show');
    });



    /*
    |--------------------------------------------------------------------------
    | Videos
    |--------------------------------------------------------------------------
    */

    Route::prefix('videos')->controller(VideoController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/{video}', 'show');
    });



    /*
    |--------------------------------------------------------------------------
    | Quizzes
    |--------------------------------------------------------------------------
    */

    Route::prefix('quizzes')->controller(QuizController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/{quiz}', 'show');


        Route::middleware('auth:sanctum')->post('/{quiz}/start', 'start');
    });
    /*
    |--------------------------------------------------------------------------
    | Quiz Attempts
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('quiz-attempts')->controller(QuizAttemptController::class)->group(function () {

        Route::post('/{attempt}/answer', 'answer');

        Route::post('/{attempt}/finish', 'finish');

        Route::get('/{attempt}/result', 'result');
    });



    /*
    |--------------------------------------------------------------------------
    | Devices
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('devices')->controller(DeviceController::class)->group(function () {

        Route::get('/', 'index');

        Route::post('/', 'store');

        Route::get('/active', 'activeDevices');

        Route::get('/{device}', 'show');

        Route::put('/{device}', 'update');

        Route::delete('/{device}', 'destroy');

        Route::patch('/{device}/activate', 'activate');

        Route::patch('/{device}/deactivate', 'deactivate');
    });



    /*
    |--------------------------------------------------------------------------
    | Plans
    |--------------------------------------------------------------------------
    */

    Route::prefix('plans')->controller(PlanController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/active', 'active');

        Route::get('/{plan}', 'show');
    });



    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('payments')->controller(PaymentController::class)->group(function () {

        Route::post('/request/{purchase}', 'requestPayment');

        Route::post('/verify/{transaction}', 'verifyPayment');

        Route::post('/refund/{transaction}', 'refund');
    });



    /*
    |--------------------------------------------------------------------------
    | Payment Callback
    |--------------------------------------------------------------------------
    */

    Route::match(['GET', 'POST'], '/payment/callback', PaymentCallbackController::class);



    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('purchases')->controller(PurchaseController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/paid', 'paid');

        Route::get('/pending', 'pending');

        Route::get('/{purchase}', 'show');

        Route::post('/', 'store');

        Route::delete('/{purchase}', 'destroy');
    });



    /*
    |--------------------------------------------------------------------------
    | Purchase Items
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('purchase-items')->controller(PurchaseItemController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/{purchaseItem}', 'show');
    });



    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('subscriptions')->controller(SubscriptionController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/active', 'active');

        Route::get('/expired', 'expired');

        Route::get('/cancelled', 'cancelled');

        Route::get('/{subscription}', 'show');

        Route::patch('/{subscription}/activate', 'activate');

        Route::patch('/{subscription}/cancel', 'cancel');

        Route::patch('/{subscription}/extend', 'extend');
    });



    /*
    |--------------------------------------------------------------------------
    | Payment Transactions
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('payment-transactions')->controller(PaymentTransactionController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/{paymentTransaction}', 'show');
    });
});
