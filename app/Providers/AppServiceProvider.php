<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

// Grade
use App\Repositories\Eloquent\GradeRepository;
use App\Repositories\Interfaces\GradeRepositoryInterface;

// Subject
use App\Repositories\Eloquent\SubjectRepository;
use App\Repositories\Interfaces\SubjectRepositoryInterface;

// Book
use App\Repositories\Eloquent\BookRepository;
use App\Repositories\Interfaces\BookRepositoryInterface;

// Chapter
use App\Repositories\Eloquent\ChapterRepository;
use App\Repositories\Interfaces\ChapterRepositoryInterface;

// Section
use App\Repositories\Eloquent\SectionRepository;
use App\Repositories\Interfaces\SectionRepositoryInterface;

// Content Item
use App\Repositories\Eloquent\ContentItemRepository;
use App\Repositories\Interfaces\ContentItemRepositoryInterface;

// Video
use App\Repositories\Eloquent\VideoRepository;
use App\Repositories\Interfaces\VideoRepositoryInterface;

// Pdf File
use App\Repositories\Eloquent\PdfFileRepository;
use App\Repositories\Interfaces\PdfFileRepositoryInterface;

// Step By Step Page
use App\Repositories\Eloquent\StepByStepPageRepository;
use App\Repositories\Interfaces\StepByStepPageRepositoryInterface;

// Sample Question
use App\Repositories\Eloquent\SampleQuestionRepository;
use App\Repositories\Interfaces\SampleQuestionRepositoryInterface;

// Quiz
use App\Repositories\Eloquent\QuizRepository;
use App\Repositories\Interfaces\QuizRepositoryInterface;

// Question
use App\Repositories\Eloquent\QuestionRepository;
use App\Repositories\Interfaces\QuestionRepositoryInterface;

// Question Option
use App\Repositories\Eloquent\QuestionOptionRepository;
use App\Repositories\Interfaces\QuestionOptionRepositoryInterface;

// Quiz Attempt
use App\Repositories\Eloquent\QuizAttemptRepository;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;

// Question Attempt
use App\Repositories\Eloquent\QuestionAttemptRepository;
use App\Repositories\Interfaces\QuestionAttemptRepositoryInterface;

// OTP
use App\Repositories\Eloquent\OtpCodeRepository;
use App\Repositories\Interfaces\OtpCodeRepositoryInterface;

// Device
use App\Repositories\Eloquent\DeviceRepository;
use App\Repositories\Interfaces\DeviceRepositoryInterface;

// SMS
use App\Services\Sms\Contracts\SmsProviderInterface;
use App\Services\Sms\Providers\MockSmsProvider;

// Plan
use App\Repositories\Eloquent\PlanRepository;
use App\Repositories\Interfaces\PlanRepositoryInterface;

// Purchase
use App\Repositories\Eloquent\PurchaseRepository;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;

// Purchase Item
use App\Repositories\Eloquent\PurchaseItemRepository;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;

// Subscription
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

// Payment Transaction
use App\Repositories\Eloquent\PaymentTransactionRepository;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Providers\ZibalProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * ثبت وابستگی‌ها در Service Container
     */
    public function register(): void
    {
        // Grade
        $this->app->singleton(
            GradeRepositoryInterface::class,
            GradeRepository::class
        );

        // Subject
        $this->app->singleton(
            SubjectRepositoryInterface::class,
            SubjectRepository::class
        );

        // Book
        $this->app->singleton(
            BookRepositoryInterface::class,
            BookRepository::class
        );

        // Chapter
        $this->app->singleton(
            ChapterRepositoryInterface::class,
            ChapterRepository::class
        );

        // Section
        $this->app->singleton(
            SectionRepositoryInterface::class,
            SectionRepository::class
        );

        // Content Item
        $this->app->singleton(
            ContentItemRepositoryInterface::class,
            ContentItemRepository::class
        );

        // Video
        $this->app->singleton(
            VideoRepositoryInterface::class,
            VideoRepository::class
        );

        // PDF File
        $this->app->singleton(
            PdfFileRepositoryInterface::class,
            PdfFileRepository::class
        );

        // Step By Step Page
        $this->app->singleton(
            StepByStepPageRepositoryInterface::class,
            StepByStepPageRepository::class
        );

        // Sample Question
        $this->app->singleton(
            SampleQuestionRepositoryInterface::class,
            SampleQuestionRepository::class
        );

        // Quiz
        $this->app->singleton(
            QuizRepositoryInterface::class,
            QuizRepository::class
        );

        // Question
        $this->app->singleton(
            QuestionRepositoryInterface::class,
            QuestionRepository::class
        );

        // Question Option
        $this->app->singleton(
            QuestionOptionRepositoryInterface::class,
            QuestionOptionRepository::class
        );

        // Quiz Attempt
        $this->app->singleton(
            QuizAttemptRepositoryInterface::class,
            QuizAttemptRepository::class
        );

        // Question Attempt
        $this->app->singleton(
            QuestionAttemptRepositoryInterface::class,
            QuestionAttemptRepository::class
        );

        // OTP
        $this->app->singleton(
            OtpCodeRepositoryInterface::class,
            OtpCodeRepository::class
        );

        // Device
        $this->app->singleton(
            DeviceRepositoryInterface::class,
            DeviceRepository::class
        );

        // Plan
        $this->app->singleton(
            PlanRepositoryInterface::class,
            PlanRepository::class
        );

        // Purchase
        $this->app->singleton(
            PurchaseRepositoryInterface::class,
            PurchaseRepository::class
        );

        // Purchase Item
        $this->app->singleton(
            PurchaseItemRepositoryInterface::class,
            PurchaseItemRepository::class
        );

        // Subscription
        $this->app->singleton(
            SubscriptionRepositoryInterface::class,
            SubscriptionRepository::class
        );

        // Payment Transaction
        $this->app->singleton(
            PaymentTransactionRepositoryInterface::class,
            PaymentTransactionRepository::class
        );




        /*
        |--------------------------------------------------------------------------
        | SMS Provider
        |--------------------------------------------------------------------------
        | فعلاً از Mock استفاده می‌کنیم.
        | بعداً فقط همین خط را به Provider واقعی تغییر می‌دهیم.
        |
        | مثال:
        | GhasedakProvider::class
        | KavenegarProvider::class
        | MeliPayamakProvider::class
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(
            SmsProviderInterface::class,
            MockSmsProvider::class
        );
        

        /*|--------------------------------------------------------------------------
        | Payment Gateway
        |--------------------------------------------------------------------------
        | فعلاً از زیبال استفاده می‌کنیم.
        | بعداً فقط همین خط را به Provider جدید تغییر می‌دهیم.
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(
            PaymentGatewayInterface::class,
            ZibalProvider::class
        );
    }

    /**
     * تنظیمات اولیه برنامه
     */
    public function boot(): void
    {
        RateLimiter::for(
            'send-otp',
            function (Request $request) {

                return [

                    // محدودیت بر اساس IP
                    Limit::perMinutes(15, 5)
                        ->by($request->ip()),

                    // محدودیت بر اساس شماره موبایل
                    Limit::perMinutes(15, 5)
                        ->by($request->input('mobile')),

                ];
            }
        );
    }
}
