<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

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
use App\Http\Controllers\Api\V1\StudentDashboardController;
use App\Http\Controllers\Api\V1\ContentSearchController;
use App\Http\Controllers\Api\V1\PowerpointStoreController;
use App\Http\Controllers\Api\V1\AdvertisementController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentCallbackController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\PurchaseItemController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\PaymentTransactionController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\PdfFileController;


Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        DB::select('select 1');
        return \App\Helpers\ApiResponse::success([
            'status' => 'healthy',
            'time' => now()->toIso8601String(),
            'environment' => app()->environment(),
        ], 'سامانه در دسترس است.');
    });

    Route::middleware('signed')->prefix('pdf-files')->controller(PdfFileController::class)->group(function () {
        Route::get('/{pdfFile}/view', 'viewFile')->name('pdf-files.view');
    });


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
    | نکته‌ی مهم: این بلوک قبلاً هیچ میان‌افزار auth:sanctum نداشت!
    | یعنی توکن کاربر اصلاً بررسی نمی‌شد و auth()->user() همیشه
    | خالی می‌ماند — Policy هم چون کاربر نداشت، همیشه به‌صورت
    | خودکار (و بی‌جزئیات) رد می‌کرد. همین دقیقاً باعث ۴۰۳ی می‌شد
    | که هیچ‌جوره با تست‌های Tinker (که کاربر واقعی داشت) جور
    | درنمی‌آمد.
    */

    /*
    |--------------------------------------------------------------------------
    | مرور ساختار آموزشی — بدون نیاز به ورود
    |--------------------------------------------------------------------------
    | طبق طراحی اولیه‌ی پروژه، کاربر تا قبل از خرید نیازی به ورود
    | ندارد و باید بتواند آزادانه پایه‌ها، دروس، کتاب‌ها، فصل‌ها،
    | بخش‌ها، و معلم‌ها را مرور کند — فقط وقتی به محتوای پولی یا
    | خرید می‌رسد، به OTP نیاز پیدا می‌کند. برای همین این گروه،
    | برخلاف قبل، دیگر پشت auth:sanctum نیست.
    */

    Route::apiResource('grades', GradeController::class)
        ->only(['index', 'show']);

    Route::apiResource('subjects', SubjectController::class)
        ->only(['index', 'show']);

    Route::apiResource('books', BookController::class)
        ->only(['index', 'show']);

    // مسیر انتخاب دانش‌آموز: پایه→معلم (ابتدایی) و کتاب→معلم
    // (متوسطه، وقتی یک کتاب چند معلم داشته باشد).
    Route::get('/grades/{grade}/teachers', [TeacherController::class, 'forGrade']);

    Route::get('/teachers/{teacher}/books', [TeacherController::class, 'books']);

    Route::get('/books/{book}/teachers', [BookController::class, 'teachers']);

    Route::get('/books/{book}/quiz-summary', [BookController::class, 'quizSummary']);

    Route::apiResource('chapters', ChapterController::class)
        ->only(['index', 'show']);

    Route::apiResource('sections', SectionController::class)
        ->only(['index', 'show']);



    /*
    |--------------------------------------------------------------------------
    | Content Items
    |--------------------------------------------------------------------------
    | خودِ محتوا هم دیگر پشت ورود اجباری نیست — چون محتوای رایگان
    | (۳ ویدئوی اول هر کتاب و غیره) باید بدون ورود قابل‌دیدن باشد.
    | تشخیص رایگان/پولی‌بودن و نمایش/عدم‌نمایش لینک واقعی فایل،
    | داخل خودِ ContentItemResource انجام می‌شود — اگر کاربر وارد
    | شده باشد (توکن Sanctum معتبر بفرستد)، آن هم همانجا تشخیص داده
    | می‌شود، بدون این‌که ورود شرط لازم برای اصل دسترسی به مسیر
    | باشد.
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

    Route::middleware('auth:sanctum')->prefix('videos')->controller(VideoController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/{video}', 'show');
    });



    /*
    |--------------------------------------------------------------------------
    | Quizzes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('quizzes')->controller(QuizController::class)->group(function () {

        Route::get('/', 'index');

        Route::get('/{quiz}', 'show');


        Route::post('/{quiz}/start', 'start');
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

    Route::middleware('auth:sanctum')->prefix('student')->controller(StudentDashboardController::class)->group(function () {
        Route::get('/dashboard', 'show');
        Route::get('/progress/content/{contentItem}', 'contentProgress');
        Route::post('/progress/content/{contentItem}', 'saveProgress');
    });

    Route::get('/search/content', ContentSearchController::class);

    Route::get('/powerpoints', [PowerpointStoreController::class, 'index']);
    Route::get('/advertisements', [AdvertisementController::class, 'index']);
    Route::post('/advertisements/{advertisement}/view', [AdvertisementController::class, 'view']);
    Route::post('/advertisements/{advertisement}/click', [AdvertisementController::class, 'click']);
    Route::middleware('auth:sanctum')->prefix('powerpoints')->controller(PowerpointStoreController::class)->group(function () {
        Route::get('/orders', 'orders');
        Route::post('/{powerpoint}/purchase', 'purchase');
        Route::get('/{powerpoint}/download', 'download');
        Route::delete('/purchases/{purchase}/cancel', 'cancel');
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

    Route::get('/payment/mode', [PaymentController::class, 'mode']);

    Route::middleware('auth:sanctum')->prefix('payments')->controller(PaymentController::class)->group(function () {

        Route::post('/request/{purchase}', 'requestPayment');

        Route::post('/test-complete/{purchase}', 'completeTest');

        Route::post('/test-fail/{purchase}', 'failTest');

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
