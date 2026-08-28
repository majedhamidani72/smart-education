<?php

namespace App\Providers;

use App\Repositories\Eloquent\BookRepository;
use App\Repositories\Eloquent\ChapterRepository;
use App\Repositories\Eloquent\ContentItemRepository;
use App\Repositories\Eloquent\DeviceRepository;
use App\Repositories\Eloquent\GradeRepository;
use App\Repositories\Eloquent\OtpCodeRepository;
use App\Repositories\Eloquent\PaymentTransactionRepository;
use App\Repositories\Eloquent\PdfFileRepository;
use App\Repositories\Eloquent\PlanRepository;
use App\Repositories\Eloquent\PurchaseItemRepository;
use App\Repositories\Eloquent\PurchaseRepository;
use App\Repositories\Eloquent\QuestionAttemptRepository;
use App\Repositories\Eloquent\QuestionOptionRepository;
use App\Repositories\Eloquent\QuestionRepository;
use App\Repositories\Eloquent\QuizAttemptRepository;
use App\Repositories\Eloquent\QuizRepository;
use App\Repositories\Eloquent\SampleQuestionRepository;
use App\Repositories\Eloquent\SectionRepository;
use App\Repositories\Eloquent\SettingRepository;
use App\Repositories\Eloquent\StepByStepPageRepository;
use App\Repositories\Eloquent\SubjectRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\TeacherAssignmentRepository;
use App\Repositories\Eloquent\TeacherEarningRepository;
use App\Repositories\Eloquent\VideoRepository;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\ChapterRepositoryInterface;
use App\Repositories\Interfaces\ContentItemRepositoryInterface;
use App\Repositories\Interfaces\DeviceRepositoryInterface;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Repositories\Interfaces\OtpCodeRepositoryInterface;
use App\Repositories\Interfaces\PaymentTransactionRepositoryInterface;
use App\Repositories\Interfaces\PdfFileRepositoryInterface;
use App\Repositories\Interfaces\PlanRepositoryInterface;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Repositories\Interfaces\QuestionAttemptRepositoryInterface;
use App\Repositories\Interfaces\QuestionOptionRepositoryInterface;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Repositories\Interfaces\QuizAttemptRepositoryInterface;
use App\Repositories\Interfaces\QuizRepositoryInterface;
use App\Repositories\Interfaces\SampleQuestionRepositoryInterface;
use App\Repositories\Interfaces\SectionRepositoryInterface;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Interfaces\StepByStepPageRepositoryInterface;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Repositories\Interfaces\TeacherAssignmentRepositoryInterface;
use App\Repositories\Interfaces\TeacherEarningRepositoryInterface;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Providers\ZibalProvider;
use App\Services\Sms\Contracts\SmsProviderInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Services\Sms\Providers\KavenegarProvider;

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

        // Teacher Assignment
        $this->app->singleton(
            TeacherAssignmentRepositoryInterface::class,
            TeacherAssignmentRepository::class
        );

        // Teacher Earning
        $this->app->singleton(
            TeacherEarningRepositoryInterface::class,
            TeacherEarningRepository::class
        );

        $this->app->bind(
            SettingRepositoryInterface::class,
            SettingRepository::class
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
            KavenegarProvider::class
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
