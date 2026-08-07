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

class AppServiceProvider extends ServiceProvider
{
    /**
     * ثبت وابستگی‌ها در Service Container
     */
    public function register(): void
    {
        // Grade
        $this->app->bind(
            GradeRepositoryInterface::class,
            GradeRepository::class
        );

        // Subject
        $this->app->bind(
            SubjectRepositoryInterface::class,
            SubjectRepository::class
        );

        // Book
        $this->app->bind(
            BookRepositoryInterface::class,
            BookRepository::class
        );

        // Chapter
        $this->app->bind(
            ChapterRepositoryInterface::class,
            ChapterRepository::class
        );

        // Section
        $this->app->bind(
            SectionRepositoryInterface::class,
            SectionRepository::class
        );

        // Content Item
        $this->app->bind(
            ContentItemRepositoryInterface::class,
            ContentItemRepository::class
        );

        // Video
        $this->app->bind(
            VideoRepositoryInterface::class,
            VideoRepository::class
        );

        // PDF File
        $this->app->bind(
            PdfFileRepositoryInterface::class,
            PdfFileRepository::class
        );

        // Step By Step Page
        $this->app->bind(
            StepByStepPageRepositoryInterface::class,
            StepByStepPageRepository::class
        );

        // Sample Question
        $this->app->bind(
            SampleQuestionRepositoryInterface::class,
            SampleQuestionRepository::class
        );

        // Quiz
        $this->app->bind(
            QuizRepositoryInterface::class,
            QuizRepository::class
        );

        // Question
        $this->app->bind(
            QuestionRepositoryInterface::class,
            QuestionRepository::class
        );

        // Question Option
        $this->app->bind(
            QuestionOptionRepositoryInterface::class,
            QuestionOptionRepository::class
        );

        // Quiz Attempt
        $this->app->bind(
            QuizAttemptRepositoryInterface::class,
            QuizAttemptRepository::class
        );

        // Question Attempt
        $this->app->bind(
            QuestionAttemptRepositoryInterface::class,
            QuestionAttemptRepository::class
        );

        // OTP
        $this->app->bind(
            OtpCodeRepositoryInterface::class,
            OtpCodeRepository::class
        );

        // Device
        $this->app->bind(
            DeviceRepositoryInterface::class,
            DeviceRepository::class
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
        $this->app->bind(
            SmsProviderInterface::class,
            MockSmsProvider::class
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
